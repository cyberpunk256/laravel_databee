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
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ["required", "in:" . implode(",", array_column(config('values.enums.media_types'), 'value'))],
            'url' => ['required', 'string', 'max:255'],
            'gpx_url' => ['nullable', 'required_if:type=1', 'string', 'max:255'], // movie
            'video_time' => ['nullable', 'required_if:type=1'] // movie
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'メディア名',
            'type' => 'メディア種別',
            'url' => 'メディアファイル',
            'gpx_url' => 'GPXファイル',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
        ];
    }
}
