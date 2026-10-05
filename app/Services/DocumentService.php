<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function __construct(private ActivityLogger $activity) {}

    public function upload(array $data, UploadedFile $file, User $user): Document
    {
        $path = $file->store('documents', 'local');

        try {
            $document = Document::create([
                'title' => $data['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'department_id' => $data['department_id'],
                'folder_id' => $data['folder_id'],
                'uploaded_by_id' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $this->activity->log('created', $document, "Mengunggah file \"{$document->original_name}\"");

        return $document;
    }

    public function update(Document $document, array $data): Document
    {
        $document->update($data);
        $this->activity->log('updated', $document, "Mengubah informasi file \"{$document->original_name}\"");

        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
        $this->activity->log('deleted', $document, "Menghapus file \"{$document->original_name}\"");
    }

    public function restore(int $id): Document
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $document->restore();
        $this->activity->log('restored', $document, "Memulihkan file \"{$document->original_name}\"");

        return $document;
    }

    public function forceDelete(int $id): void
    {
        $document = Document::onlyTrashed()->findOrFail($id);
        $path = $document->storage_path;
        $name = $document->original_name;
        $document->forceDelete();
        Storage::disk('local')->delete($path);
        $this->activity->log('force_deleted', $document, "Menghapus permanen file \"{$name}\"");
    }
}
