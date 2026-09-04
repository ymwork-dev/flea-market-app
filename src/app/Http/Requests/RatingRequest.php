<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => '評価を選択してください。',
            'score.between'  => '評価は1〜5の範囲で選んでください。',
            'comment.max'    => 'コメントは255文字以内で入力してください。',
        ];
    }
}
