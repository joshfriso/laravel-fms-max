<?php

namespace App\Repositories;

use App\Models\Department;

class DepartmentRepository
{
    public function options()
    {
        return Department::orderBy('name')->get(['id', 'name']);
    }

    public function paginate()
    {
        return Department::withCount('documents')->orderBy('name')->paginate(10);
    }
}
