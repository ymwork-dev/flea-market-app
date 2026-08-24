<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'img_url'     => ['required', 'image', 'mimes:jpeg,png', 'max:2048'],
            'category_ids'    => ['required'],
            'condition'   => ['required'],
            'brand'       => ['nullable', 'string', 'max:255'],
            'price'       => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => '商品名を入力してください。',
            'description.required' => '商品の説明を入力してください。',
            'description.max'      => '商品の説明は255文字以内で入力してください。',
            'img_url.required'     => '商品画像を選択してください。',
            'img_url.image'        => '画像ファイルを選択してください。',
            'img_url.mimes'        => '商品画像の拡張子は .jpeg もしくは .png のみ有効です。',
            'category_ids.required'    => '商品のカテゴリーを選択してください。',
            'condition.required'   => '商品の状態を選択してください。',
            'price.required'       => '販売価格を入力してください。',
            'price.integer'        => '販売価格は数値で入力してください。',
            'price.min'            => '販売価格は0円以上で入力してください。',
        ];
    }
}
