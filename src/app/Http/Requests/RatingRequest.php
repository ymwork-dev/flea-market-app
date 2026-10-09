<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// 商品受け取り後の評価ルールを定義するクラス
class RatingRequest extends FormRequest
{
    // ログインユーザーに権限があるか確認
    public function authorize(): bool
    {
        // 常に許可
        return true;
    }

    // 受取評価のルールを定義するメソッド
    public function rules(): array
    {
        return [
            // 評価は必須、整数、1から5の範囲
            'score' => ['required', 'integer', 'between:1,5'],
            // コメントは空でもよい、文字列、255字以内
            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }

    // メッセージを定義するメソッド
    public function messages(): array
    {
        return [
            'score.required' => '評価を選択してください。',
            'score.between'  => '評価は1〜5の範囲で選んでください。',
            'comment.max'    => 'コメントは255文字以内で入力してください。',
        ];
    }
}
