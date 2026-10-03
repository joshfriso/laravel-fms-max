<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Services\DepartmentService;
use App\Services\DocumentService;
use App\Services\FolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TrashController extends Controller
{
    public function __construct(
        private FolderService $folders,
        private DocumentService $documents,
        private DepartmentService $departments,
    ) {}

    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('Trash/Index', [
            'folders' => Folder::onlyTrashed()->orderByDesc('deleted_at')->get(),
            'documents' => Document::onlyTrashed()->orderByDesc('deleted_at')->get(),
            'departments' => Department::onlyTrashed()->orderByDesc('deleted_at')->get(),
        ]);
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        Gate::authorize('admin');
        match ($type) {
            'folders' => $this->folders->restore($id),
            'documents' => $this->documents->restore($id),
            'departments' => $this->departments->restore($id),
            default => abort(404),
        };

        return back()->with('success', 'Item restored.');
    }

    public function forceDelete(string $type, int $id): RedirectResponse
    {
        Gate::authorize('admin');
        match ($type) {
            'folders' => $this->folders->forceDelete($id),
            'documents' => $this->documents->forceDelete($id),
            'departments' => $this->departments->forceDelete($id),
            default => abort(404),
        };

        return back()->with('success', 'Item permanently deleted.');
    }
}
