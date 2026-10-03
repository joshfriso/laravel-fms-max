<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const FILE_TYPE_CATEGORIES = [
        'Dokumen' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        'Spreadsheet' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'Gambar' => ['image/jpeg', 'image/png'],
    ];

    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'totalFolders' => Folder::count(),
            'totalFiles' => Document::count(),
            'totalDepartments' => Department::count(),
            'recentFiles' => Document::with(['folder', 'department', 'uploader'])->latest()->limit(10)->get(),
            'fileTypes' => $this->fileTypeBreakdown(),
        ]);
    }

    private function fileTypeBreakdown(): array
    {
        return collect(self::FILE_TYPE_CATEGORIES)
            ->map(fn ($mimeTypes, $label) => [
                'label' => $label,
                'count' => Document::whereIn('mime_type', $mimeTypes)->count(),
            ])
            ->values()
            ->all();
    }
}
