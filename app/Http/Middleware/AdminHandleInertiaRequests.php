<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class AdminHandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $enums = config('values.enums');
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user('admin'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'csrf_token' => csrf_token(),
            'enums' => $enums,
            'bucket_path' => "https://" . config('filesystems.disks.s3.bucket') . ".s3." . config('filesystems.disks.s3.region') . "." . "amazonaws.com/",
        ]);
    }
}
