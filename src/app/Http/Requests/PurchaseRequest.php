<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $item = Item::findOrFail($this->route('item_id'));

            if ($item->user_id === Auth::id()) {
                $validator->errors()->add('error', '自分の商品は購入できません');
            }

            if ($item->is_sold) {
                $validator->errors()->add('error', 'この商品は既に売り切れています');
            }
        });
    }
}

