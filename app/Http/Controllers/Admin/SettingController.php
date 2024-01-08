<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Setting;
use App\Http\Requests\Admin\SettingUpdateRequest;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return Inertia::render('Admin/Setting/Index', [
            'settings' => $settings
        ]);
    }

    public function update(SettingUpdateRequest $request) {
        try {
            \DB::beginTransaction();
            
            $data = $request->only(['map_gpx_weight', 'map_default_zoom', 's3_upload_folder', 'media_conver_options']);
            foreach($data as $key => $value) {
                if($key == 'media_conver_options') {
                    $value = json_encode($value);
                }
                Setting::where('key', $key)->update(['value' => $value]);
            }

            \DB::commit();
            return redirect()->route('admin.setting.index')->with(['success' => __("success_update")]);
        } catch (\Throwable $exception) {
            \DB::rollBack();
            \Log::error($exception);
            return back()->with(['error' => __("success_error")], 404);
        }
    }
}