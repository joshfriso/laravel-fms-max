<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Repositories\DepartmentRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\FolderRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    private const PREVIEWABLE_MIME_TYPES = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    public function __construct(
        private DocumentService $documents,
        private DocumentRepository $documentRepository,
        private DepartmentRepository $departmentRepository,
        private FolderRepository $folderRepository,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);
        $documents = $this->documentRepository->paginate($filters);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $filters,
            'departments' => $this->departmentRepository->options(),
            'folders' => $this->folderRepository->options(),
            'canManage' => $request->user()->isAdministrator(),
        ]);
    }

    public function show(Document $document): Response
    {
        return Inertia::render('Documents/Show', [
            'document' => $document->load(['folder', 'department', 'uploader']),
            'canPreview' => in_array($document->mime_type, self::PREVIEWABLE_MIME_TYPES, true),
            'canManage' => request()->user()->isAdministrator(),
            'departments' => $this->departmentRepository->options(),
            'folders' => $this->folderRepository->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'folder_id' => ['required', 'exists:folders,id'],
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,txt'],
        ]);
        $this->documents->upload($data, $data['file'], $request->user());

        return to_route('documents.index')->with('success', 'File uploaded.');
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'folder_id' => ['required', 'exists:folders,id'],
        ]);
        $this->documents->update($document, $data);

        return to_route('documents.show', $document)->with('success', 'File updated.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('admin');
        $this->documents->delete($document);

        return to_route('documents.index')->with('success', 'File deleted.');
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);

        return Storage::disk('local')->download($document->storage_path, $document->original_name);
    }

    public function preview(Document $document): StreamedResponse
    {
        abort_unless(in_array($document->mime_type, self::PREVIEWABLE_MIME_TYPES, true), 404);
        abort_unless(Storage::disk('local')->exists($document->storage_path), 404);

        return Storage::disk('local')->response($document->storage_path, $document->original_name);
    }
}
