<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_administrator_can_create_nested_folders_and_cannot_make_a_cycle(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $root = Folder::create(['name' => 'Legal', 'created_by_id' => $admin->id]);

        $this->actingAs($admin)->post(route('folders.store'), ['name' => 'Contracts', 'parent_id' => $root->id])
            ->assertRedirect();

        $child = Folder::where('name', 'Contracts')->firstOrFail();
        $this->assertSame($root->id, $child->parent_id);

        $this->actingAs($admin)->patch(route('folders.update', $root), [
            'name' => 'Legal', 'parent_id' => $child->id,
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_viewer_can_browse_and_download_but_cannot_change_data(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'administrator']);
        $viewer = User::factory()->create(['role' => 'viewer']);
        $department = Department::create(['name' => 'Finance']);
        $folder = Folder::create(['name' => 'Reports', 'created_by_id' => $admin->id]);
        $document = Document::create([
            'title' => 'Budget', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'original_name' => 'budget.pdf', 'storage_path' => 'documents/budget.pdf',
            'mime_type' => 'application/pdf', 'size' => 10, 'uploaded_by_id' => $admin->id,
        ]);
        Storage::disk('local')->put($document->storage_path, 'test');

        $this->actingAs($viewer)->get(route('folders.index'))->assertOk();
        $this->actingAs($viewer)->get(route('documents.show', $document))->assertOk();
        $this->actingAs($viewer)->get(route('documents.download', $document))->assertDownload('budget.pdf');
        $this->actingAs($viewer)->post(route('folders.store'), ['name' => 'Denied'])->assertForbidden();
        $this->actingAs($viewer)->delete(route('documents.destroy', $document))->assertForbidden();
        $this->actingAs($viewer)->post(route('departments.store'), ['name' => 'Denied'])->assertForbidden();
    }

    public function test_preview_renders_pdf_inline(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'Finance']);
        $folder = Folder::create(['name' => 'Reports', 'created_by_id' => $admin->id]);
        $document = Document::create([
            'title' => 'Budget', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'original_name' => 'budget.pdf', 'storage_path' => 'documents/budget.pdf',
            'mime_type' => 'application/pdf', 'size' => 10, 'uploaded_by_id' => $admin->id,
        ]);
        Storage::disk('local')->put($document->storage_path, '%PDF-1.4 test');

        $response = $this->actingAs($admin)->get(route('documents.preview', $document))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('inline;', $response->headers->get('content-disposition'));
    }

    public function test_administrator_uploads_edits_searches_and_deletes_a_document(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'HR']);
        $folder = Folder::create(['name' => 'Policies', 'created_by_id' => $admin->id]);

        $this->actingAs($admin)->post(route('documents.store'), [
            'title' => 'Leave Policy', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'files' => [UploadedFile::fake()->create('leave.pdf', 10, 'application/pdf')],
        ])->assertRedirect();

        $document = Document::firstOrFail();
        $this->assertSame('leave.pdf', $document->original_name);
        Storage::disk('local')->assertExists($document->storage_path);

        $this->actingAs($admin)->patch(route('documents.update', $document), [
            'title' => 'Annual Leave', 'department_id' => $department->id, 'folder_id' => $folder->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('documents', ['id' => $document->id, 'title' => 'Annual Leave']);
        $this->actingAs($admin)->get(route('documents.index', ['search' => 'leave']))->assertOk()->assertSee('Annual Leave');

        $this->actingAs($admin)->delete(route('documents.destroy', $document))->assertRedirect();
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
        Storage::disk('local')->assertExists($document->storage_path);
    }

    public function test_administrator_can_upload_multiple_documents_at_once(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'HR']);
        $folder = Folder::create(['name' => 'Policies', 'created_by_id' => $admin->id]);

        $this->actingAs($admin)->post(route('documents.store'), [
            'department_id' => $department->id,
            'folder_id' => $folder->id,
            'files' => [
                UploadedFile::fake()->create('leave.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('benefit.pdf', 10, 'application/pdf'),
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('documents', ['title' => 'leave', 'original_name' => 'leave.pdf']);
        $this->assertDatabaseHas('documents', ['title' => 'benefit', 'original_name' => 'benefit.pdf']);

        Document::all()->each(fn (Document $document) => Storage::disk('local')->assertExists($document->storage_path));
    }

    public function test_invalid_upload_and_deletion_of_used_department_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'Legal']);
        $folder = Folder::create(['name' => 'Policies', 'created_by_id' => $admin->id]);

        $this->actingAs($admin)->post(route('documents.store'), [
            'title' => 'Bad', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'files' => [UploadedFile::fake()->create('script.php', 1, 'application/x-php')],
        ])->assertSessionHasErrors('files.0');

        Document::create([
            'title' => 'Policy', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'original_name' => 'policy.pdf', 'storage_path' => 'documents/policy.pdf',
            'mime_type' => 'application/pdf', 'size' => 10, 'uploaded_by_id' => $admin->id,
        ]);
        $this->actingAs($admin)->delete(route('departments.destroy', $department))->assertSessionHasErrors();
        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_dashboard_shows_totals_and_only_ten_recent_files(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'Operations']);
        $folder = Folder::create(['name' => 'Manuals', 'created_by_id' => $admin->id]);
        foreach (range(1, 11) as $number) {
            Document::create([
                'title' => "Manual {$number}", 'department_id' => $department->id, 'folder_id' => $folder->id,
                'original_name' => "manual{$number}.pdf", 'storage_path' => "documents/manual{$number}.pdf",
                'mime_type' => 'application/pdf', 'size' => 10, 'uploaded_by_id' => $admin->id,
            ]);
        }

        $this->actingAs($admin)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')->where('totalFolders', 1)->where('totalFiles', 11)
            ->where('totalDepartments', 1)->has('recentFiles', 10));
    }

    public function test_departments_can_be_managed_and_nonempty_folders_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->post(route('departments.store'), ['name' => 'Human Resources'])->assertRedirect();
        $department = Department::firstOrFail();
        $this->actingAs($admin)->patch(route('departments.update', $department), ['name' => 'HR'])->assertRedirect();
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'HR']);
        $this->actingAs($admin)->delete(route('departments.destroy', $department))->assertRedirect();
        $this->assertSoftDeleted('departments', ['id' => $department->id]);

        $parent = Folder::create(['name' => 'Parent', 'created_by_id' => $admin->id]);
        Folder::create(['name' => 'Child', 'parent_id' => $parent->id, 'created_by_id' => $admin->id]);
        $this->actingAs($admin)->delete(route('folders.destroy', $parent))->assertSessionHasErrors('folder');
        $this->assertDatabaseHas('folders', ['id' => $parent->id]);
    }
}
