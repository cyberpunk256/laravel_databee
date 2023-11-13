<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'type' => ["required", "in:" . implode(",", array_column(config('constant.enums.media_types'), 'value'))],
            // 'video' => ['nullable', 'required_if:type,1'], // movie,
            // 'gpx' => ['nullable', 'required_if:type,1'], // gpx,
            // 'image' => ['nullable', 'required_unless:type,1'], // image,
            'image_lat' => ['nullable'],
            'image_long' => ['nullable'],
            'origin_video_path' => ['nullable'],
            'origin_image_path' => ['nullable'],
            'origin_gpx_path' => ['nullable'],
        ];
        if($this->origin_video_path == null) {
            $rules['video'] = ['nullable', 'required_if:type,1'];
        }
        if($this->origin_gpx_path == null) {
            $rules['gpx'] = ['nullable', 'required_if:type,1'];
        }
        if($this->origin_image_path == null) {
            $rules['image'] = ['nullable', 'required_unless:type,1'];
        }
        return $rules;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [
            'name' => 'メディア名',
            'type' => 'メディア種別',
            'video' => '3DMovieファイル',
            'gpx' => 'GPXファイル',
        ];
        if($this->type == 2) {
            $attributes['image'] = 'Still Imageファイル';
        } else if($this->type == 3) {
            $attributes['image'] = 'Panorama Imageファイル';
        }
        return $attributes;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_if' => ':attributeは必ず入力してください。',
        ];
    }
}
