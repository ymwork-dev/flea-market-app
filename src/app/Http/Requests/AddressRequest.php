<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// 住所変更の入力をチェックするクラス
class AddressRequest extends FormRequest
{
    // リダイレクト先を定義するメソッド
    protected function getRedirectUrl()
    {
        // URLから商品IDの取得
        $itemId = $this->route('item_id');

        // 商品の住所変更画面のURLを返す
        return url("/purchase/address/{$itemId}");
    }

    // ログインユーザーの権限を確認
    public function authorize(): bool
    {
        // 常に許可
        return true;
    }

    // 住所入力のルールを定義するメソッド
    public function rules(): array
    {
        return [
            // 郵便番号は入力必須、文字列、123-4567の形
            'postcode' => ['required', 'string', 'regex:/^\d{3}-\d{4}$/'],
            // 住所の入力必須、文字列
            'address'  => ['required', 'string'],
        ];
    }

    // エラーメッセージを定義するメソッド
    public function messages(): array
    {
        return [
            'postcode.required' => '郵便番号は入力必須です。',
            'postcode.regex'    => '郵便番号はハイフンありの8文字で入力してください（例: 123-4567）。',
            'address.required'  => '住所は入力必須です。',
        ];
    }
}
