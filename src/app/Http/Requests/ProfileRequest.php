<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// プロフィール編集画面のバリデーションルールを定義するクラス
class ProfileRequest extends FormRequest
{
    // ユーザーの権限を確認するメソッド
    public function authorize(): bool
    {
        // 常に許可
        return true;
    }

    // バリデーションルールを定義するメソッド
    public function rules(): array
    {
        // バリデーションルールの定義
        return [
            // プロフィール画像は任意、画像ファイル、jpegまたはpng形式
            'image'  => 'nullable|image|mimes:jpeg,png',
            // 名前必須、文字列、最大20文字
            'name'     => 'required|string|max:20',
            // 郵便番号必須、文字列、数字3桁-数字4桁の形式
            'postcode' => 'required|string|regex:/^\d{3}-\d{4}$/',
            // 住所必須、文字列、
            'address'  => 'required|string',
            // 建物名任意、文字列
            'building' => 'nullable|string',
        ];
    }

    // バリデーションエラーメッセージを定義するメソッド
    public function messages(): array
    {
        // バリデーションエラーメッセージの定義
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
