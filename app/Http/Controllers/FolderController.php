<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FolderController extends Controller
{
    public function __construct(private FolderService $folders) {}

    public function index(): Response
    {
        return $this->browse(null);
    }

    public function show(Folder $folder): Response
    {
        return $this->browse($folder);
    }

    private function browse(?Folder $folder): Response
    {
        return Inertia::render('Folders/Index', [
            'currentFolder' => $folder,
            'breadcrumbs' => $folder?->breadcrumbs() ?? [],
            'folders' => Folder::where('parent_id', $folder?->id)->orderBy('name')->get(),
            'documents' => $folder?->documents()->with(['department', 'uploader'])->latest()->paginate(10),
            'canManage' => request()->user()->isAdministrator(),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);
        $this->folders->create($data, $request->user());

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        Gate::authorize('admin');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:folders,id'],
        ]);
        $this->folders->rename($folder, $data);

        return back()->with('success', 'Folder updated.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        Gate::authorize('admin');
        $this->folders->delete($folder);

        return back()->with('success', 'Folder deleted.');
    }
}
