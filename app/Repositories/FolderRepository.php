<?php

namespace App\Repositories;

use App\Models\Folder;

class FolderRepository
{
    public function options()
    {
        return Folder::orderBy('name')->get(['id', 'name']);
    }

    public function roots()
    {
        return Folder::whereNull('parent_id')->with(['children', 'documents'])->orderBy('name')->get();
    }
}
