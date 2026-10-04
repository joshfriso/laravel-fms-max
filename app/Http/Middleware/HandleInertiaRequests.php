<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Blade utama Inertia.
     *
     * @var string
     */
    protected $rootView = 'app';

    /** Versi aset. */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Prop bersama.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
