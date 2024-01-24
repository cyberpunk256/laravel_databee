<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'map_gpx_weight' => ['required', 'integer', 'max:255'],
            'map_default_zoom' => ['required', 'integer', 'max:255'],
            'map_max_zoom' => ['required', 'integer', 'max:255'],
            's3_upload_folder' => ['required', 'string', 'max:255'],
            'media_conver_options' => ['required', 'array'],
            'media_conver_options.*.resolution' => ['required', 'numeric', 'min:10', 'max:5000'],
            'media_conver_options.*.bitrate' => ['required', 'numeric', 'min:10000', 'max:100000000'],
        ];
        return $rules;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'map_gpx_weight' => 'Gpxラインのウェイト',
            'map_default_zoom' => 'マップデフォルトZoom',
            'map_max_zoom' => 'マップ最大Zoom',
            's3_upload_folder' => 'S3アップロードフォルダー',
            'media_conver_options' => 'Media Convert オップション',
            'media_conver_options.*.resolution' => '解像度',
            'media_conver_options.*.bitrate' => 'Bitrate',
        ];
    }
}
