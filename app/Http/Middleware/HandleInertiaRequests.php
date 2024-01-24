<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

use App\Models\Setting;

class HandleInertiaRequests extends Middleware
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
        $map_gpx_weight = Setting::where('key', 'map_gpx_weight')->first();
        $map_default_zoom = Setting::where('key', 'map_default_zoom')->first();
        $map_max_zoom = Setting::where('key', 'map_max_zoom')->first();
        $constant = array_merge(config('constant'), [
            'bucket_path' => "https://" . config('filesystems.disks.s3.bucket') . ".s3." . config('filesystems.disks.s3.region') . "." . "amazonaws.com/",
        ]);
        $constant['map']['gpx']['weight'] = intval($map_gpx_weight->value);
        $constant['map']['zoom'] = intval($map_default_zoom->value);
        $constant['map']['max_zoom'] = intval($map_max_zoom->value);
        
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'data' => fn () => $request->session()->get('data'),
            ],
            'csrf_token' => csrf_token(),
            'constant' => $constant
        ]);
    }
}
