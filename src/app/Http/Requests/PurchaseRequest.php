<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

// 購入時の入力をチェックするクラス
class PurchaseRequest extends FormRequest
{
    // ユーザーの権限を確認するメソッド
    public function authorize(): bool
    {
        // 常に許可
        return true;
    }

    // ルールを定義するメソッド
    public function rules(): array
    {
        // 支払方法の選択必須
        return [
            'payment_method' => ['required'],
        ];
    }

    // メッセージを定義するメソッド
    public function messages(): array
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
        ];
    }

    // 普通のルールの後、追加チェックをするメソッド
    public function withValidator($validator)
    {

        // ルールチェックの後、中の処理を実行
        $validator->after(function ($validator) {

            // 商品IDから1件の商品情報を取得
            $item = Item::findOrFail($this->route('item_id'));

            // 出品者がログイン中のユーザーの場合、エラーを追加
            if ($item->user_id === Auth::id()) {
                $validator->errors()->add('error', '自分の商品は購入できません');
            }

            // 売切れの場合、エラーを追加
            if ($item->is_sold) {
                $validator->errors()->add('error', 'この商品は既に売り切れています');
            }
        });
    }
}

