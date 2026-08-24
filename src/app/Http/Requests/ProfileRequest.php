<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image'  => 'nullable|image|mimes:jpeg,png',
            'name'     => 'required|string|max:20',
            'postcode' => 'required|string|regex:/^\d{3}-\d{4}$/',
            'address'  => 'required|string',
            'building' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image'     => 'プロフィール画像には画像ファイルを指定してください。',
            'image.mimes'     => 'プロフィール画像の拡張子は .jpeg もしくは .png のみ有効です。',
            'name.required'     => 'お名前を入力してください',
            'name.max'          => 'お名前は20文字以内で入力してください。',
            'postcode.required' => '郵便番号を入力してください',
            'postcode.regex'    => '郵便番号はハイフンありの8文字で入力してください（例: 123-4567）。',
            'address.required'  => '住所を入力してください',
        ];
    }
}
