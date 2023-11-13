<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email,'.$this->id],
            'password' => [$this->id ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ["required", "in:" . implode(",", array_column(config('constant.enums.roles'), 'value'))],
            'group_id' => ['required', 'exists:groups,id'],
            'pref' => ['required'],
            'init_lat' => ['required'],
            'init_long' => ['required'],
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
            'name' => '名前',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'role' => '権限',
            'group_id' => 'グループ',
            'pref' => '都道府県',
            'init_lat' => '初期ポジション緯度',
            'init_long' => '初期ポジション緯度',
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
            //
        ];
    }
}
