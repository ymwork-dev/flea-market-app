<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    protected function getRedirectUrl()
    {
        $itemId = $this->route('item_id'); // URLから商品IDを取得
        return url("/purchase/address/{$itemId}"); // 💡実際の住所変更画面のURLに合わせてください
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'postcode' => ['required', 'string', 'regex:/^\d{3}-\d{4}$/'],
            'address'  => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'postcode.required' => '郵便番号は入力必須です。',
            'postcode.regex'    => '郵便番号はハイフンありの8文字で入力してください（例: 123-4567）。',
            'address.required'  => '住所は入力必須です。',
        ];
    }
}
