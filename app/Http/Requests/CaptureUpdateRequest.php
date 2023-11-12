<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CaptureUpdateRequest extends FormRequest
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
            'media_id' => ['required', 'exists:medias,id'],
            'playtime' => ['required'],
            'rotation' => ['required'],
            'zoom' => ['required'],
            'lat' => ['required'],
            'long' => ['required'],
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
            'media_id' => 'メディア',
            'playtime' => '時間',
            'rotation' => 'ロテーション',
            'zoom' => '縮尺',
            'lat' => '軽度',
            'long' => '緯度',
        ];
    }

}
