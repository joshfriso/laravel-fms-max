<?php

namespace App\Repositories;

use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return Document::with(['folder', 'department', 'uploader'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $pattern = '%'.strtolower($search).'%';
                $query->where(function ($query) use ($pattern) {
                    $query->whereRaw('LOWER(title) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(original_name) LIKE ?', [$pattern])
                        ->orWhereHas('department', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', [$pattern]));
                });
            })
            ->when($filters['department_id'] ?? null, fn ($query, $id) => $query->where('department_id', $id))
            ->latest()->paginate(10)->withQueryString();
    }

    public function recent(int $limit = 10)
    {
        return Document::with(['folder', 'department'])->latest()->limit($limit)->get();
    }
}
