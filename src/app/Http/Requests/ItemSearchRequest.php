<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// FormRequestクラスを継承したItemSearchRequestクラスを定義
class ItemSearchRequest extends FormRequest
{
    // authorizeは、リクエストが許可されているかどうかを判断するためのメソッド
    public function authorize()
    {
        // 全てのユーザーに許可
        return true;
    }

    // rulesは、入力チェックのルールを定義するためのメソッド
    public function rules()
    {
        // タブとキーワード入力チェックルール
        return [
            // 空でもよい、文字列であること
            'tab' => 'nullable|string',
            // 空でもよい、文字列で最大255字まで
            'keyword' => 'nullable|string|max:255',
        ];
    }

    // getTabは、リクエストからタブの値を取得するためのメソッド
    public function getTab()
    {
        // 無いときはお勧めを返す
        return $this->query('tab', 'recommend');
    }

    // getKeywordは、リクエストからキーワードの値を取得するためのメソッド
    public function getKeyword()
    {
        // 無いときは何もないを返す
        return $this->query('keyword');
    }
}
