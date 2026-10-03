<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FolderService
{
    public function __construct(private ActivityLogger $activity) {}

    public function create(array $data, User $user): Folder
    {
        $folder = Folder::create([...$data, 'created_by_id' => $user->id]);
        $this->activity->log('created', $folder, "Membuat folder \"{$folder->name}\"");

        return $folder;
    }

    public function rename(Folder $folder, array $data): Folder
    {
        $this->guardAgainstCycle($folder, $data['parent_id'] ?? null);
        $folder->update($data);
        $this->activity->log('updated', $folder, "Mengubah folder \"{$folder->name}\"");

        return $folder;
    }

    public function delete(Folder $folder): void
    {
        if ($folder->children()->exists() || $folder->documents()->exists()) {
            throw ValidationException::withMessages(['folder' => 'Empty the folder before deleting it.']);
        }
        $folder->delete();
        $this->activity->log('deleted', $folder, "Menghapus folder \"{$folder->name}\"");
    }

    public function restore(int $id): Folder
    {
        $folder = Folder::onlyTrashed()->findOrFail($id);
        $folder->restore();
        $this->activity->log('restored', $folder, "Memulihkan folder \"{$folder->name}\"");

        return $folder;
    }

    public function forceDelete(int $id): void
    {
        $folder = Folder::onlyTrashed()->findOrFail($id);
        $name = $folder->name;
        $folder->forceDelete();
        $this->activity->log('force_deleted', $folder, "Menghapus permanen folder \"{$name}\"");
    }

    private function guardAgainstCycle(Folder $folder, ?int $parentId): void
    {
        $parent = $parentId !== null ? Folder::find($parentId) : null;
        while ($parent !== null) {
            if ($parent->id === $folder->id) {
                throw ValidationException::withMessages(['parent_id' => 'A folder cannot contain itself.']);
            }
            $parent = $parent->parent;
        }
    }
}
