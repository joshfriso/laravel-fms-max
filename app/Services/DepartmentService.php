<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(private ActivityLogger $activity) {}

    public function create(array $data): Department
    {
        $department = Department::create($data);
        $this->activity->log('created', $department, "Membuat departemen \"{$department->name}\"");

        return $department;
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        $this->activity->log('updated', $department, "Mengubah departemen \"{$department->name}\"");

        return $department;
    }

    public function delete(Department $department): void
    {
        if ($department->documents()->exists()) {
            throw ValidationException::withMessages(['department' => 'Move or delete its files before deleting this department.']);
        }
        $department->delete();
        $this->activity->log('deleted', $department, "Menghapus departemen \"{$department->name}\"");
    }

    public function restore(int $id): Department
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();
        $this->activity->log('restored', $department, "Memulihkan departemen \"{$department->name}\"");

        return $department;
    }

    public function forceDelete(int $id): void
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $name = $department->name;
        $department->forceDelete();
        $this->activity->log('force_deleted', $department, "Menghapus permanen departemen \"{$name}\"");
    }
}
