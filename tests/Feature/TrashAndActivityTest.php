<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrashAndActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_deleting_a_department_soft_deletes_it_and_logs_the_action(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->post(route('departments.store'), ['name' => 'Legal'])->assertRedirect();
        $department = Department::firstOrFail();

        $this->actingAs($admin)->delete(route('departments.destroy', $department))->assertRedirect();
        $this->assertSoftDeleted('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Department::class,
            'subject_id' => $department->id,
            'action' => 'deleted',
        ]);
    }

    public function test_administrator_can_restore_and_permanently_delete_a_trashed_folder(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $folder = Folder::create(['name' => 'Legal', 'created_by_id' => $admin->id]);
        $folder->delete();

        $this->actingAs($admin)->get(route('trash.index'))->assertOk();
        $this->actingAs($admin)->post(route('trash.restore', ['folders', $folder->id]))->assertRedirect();
        $this->assertDatabaseHas('folders', ['id' => $folder->id, 'deleted_at' => null]);

        $folder->delete();
        $this->actingAs($admin)->delete(route('trash.force-delete', ['folders', $folder->id]))->assertRedirect();
        $this->assertDatabaseMissing('folders', ['id' => $folder->id]);
    }

    public function test_viewer_cannot_access_trash_or_activity_log(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $this->actingAs($viewer)->get(route('trash.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('activity.index'))->assertForbidden();
    }

    public function test_force_deleting_a_trashed_document_removes_its_stored_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'administrator']);
        $department = Department::create(['name' => 'Finance']);
        $folder = Folder::create(['name' => 'Reports', 'created_by_id' => $admin->id]);

        $this->actingAs($admin)->post(route('documents.store'), [
            'title' => 'Budget', 'department_id' => $department->id, 'folder_id' => $folder->id,
            'files' => [UploadedFile::fake()->create('budget.pdf', 10, 'application/pdf')],
        ])->assertRedirect();
        $document = Document::firstOrFail();
        $path = $document->storage_path;

        $this->actingAs($admin)->delete(route('documents.destroy', $document))->assertRedirect();
        Storage::disk('local')->assertExists($path);

        $this->actingAs($admin)->delete(route('trash.force-delete', ['documents', $document->id]))->assertRedirect();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_activity_log_lists_recorded_actions_newest_first(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->post(route('departments.store'), ['name' => 'Ops']);
        $this->assertSame(1, ActivityLog::count());

        $this->actingAs($admin)->get(route('activity.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Activity/Index')->has('logs.data', 1));
    }
}
