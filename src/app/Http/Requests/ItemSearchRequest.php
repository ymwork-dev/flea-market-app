<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemSearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tab' => 'nullable|string',
            'keyword' => 'nullable|string|max:255',
        ];
    }

    public function getTab()
    {
        return $this->query('tab', 'recommend');
    }

    public function getKeyword()
    {
        return $this->query('keyword');
    }
}
