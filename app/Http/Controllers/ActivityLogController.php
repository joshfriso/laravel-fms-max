<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('Activity/Index', [
            'logs' => ActivityLog::with('user:id,name')->latest()->paginate(20),
        ]);
    }
}
