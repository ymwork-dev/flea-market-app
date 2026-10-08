<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// 出品をチェックするためのクラス
class ExhibitionRequest extends FormRequest
{
    // このリクエストを使ってよいか決めるメソッド
    public function authorize(): bool
    {
        // 出品を許可
        return true;
    }

    // 出品ルールを定義するメソッド
    public function rules(): array
    {
        return [
            // 商品名必須、文字列、255字以内
            'name'        => ['required', 'string', 'max:255'],
            // 商品説明必須、文字列、255字以内
            'description' => ['required', 'string', 'max:255'],
            // 商品画像は出品時必須、編集時は空でもよい、画像ファイル、jpegかpng、2MBまで
            'img_url'     => [$this->isMethod('put') ? 'nullable' : 'required', 'image', 'mimes:jpeg,png', 'max:2048'],
            // 商品カテゴリ選択は必須
            'category_ids'    => ['required'],
            // 商品コンディションの入力必須
            'condition'   => ['required'],
            // ブランド名は空でもよい、文字列、255字以内
            'brand'       => ['nullable', 'string', 'max:255'],
            // 商品価格は必須、整数、0以上
            'price'       => ['required', 'integer', 'min:0'],
        ];
    }

    // メッセージを定義するメソッド
    public function messages(): array
    {
        return [
            'name.required'        => '商品名を入力してください。',
            'name.max'             => '商品名は255文字以内で入力してください。',
            'description.required' => '商品の説明を入力してください。',
            'description.max'      => '商品の説明は255文字以内で入力してください。',
            'img_url.required'     => '商品画像を選択してください。',
            'img_url.image'        => '画像ファイルを選択してください。',
            'img_url.mimes'        => '商品画像の拡張子は .jpeg もしくは .png のみ有効です。',
            'img_url.max'          => '商品画像は2MB以内のファイルを選択してください。',
            'img_url.uploaded'     => '商品画像のアップロードに失敗しました。ファイルサイズをご確認ください。',
            'brand.max'            => 'ブランド名は255文字以内で入力してください。',
            'category_ids.required'    => '商品のカテゴリーを選択してください。',
            'condition.required'   => '商品の状態を選択してください。',
            'price.required'       => '販売価格を入力してください。',
            'price.integer'        => '販売価格は数値で入力してください。',
            'price.min'            => '販売価格は0円以上で入力してください。',
        ];
    }
}
