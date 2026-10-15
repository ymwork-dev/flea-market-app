<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// コメントをチェックするためのクラス
class CommentRequest extends FormRequest
{
    // このコメントチェックを使って良いか決めるメソッド
    public function authorize(): bool
    {
        // チェックを誰でも使って良い(ログイン確認はルート)
        return true;
    }

    // コメントのルールを定義するメソッド
    public function rules(): array
    {
        // 入力必須、文字列、255字以内
        return [
            'comment' => ['required', 'string', 'max:255'],
        ];
    }

    // メッセージを定義するメソッド
    public function messages(): array
    {
        return [
            'comment.required' => 'コメントを入力してください',
            'comment.max' => 'コメントは255文字以内で入力してください',
        ];
    }
}
