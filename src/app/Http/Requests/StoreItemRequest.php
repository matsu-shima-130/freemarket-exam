<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image'       => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'condition'   => ['required', 'integer', 'in:1,2,3,4'],
            'name'        => ['required', 'string', 'max:255'],
            'brand_name'  => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price'       => ['required', 'integer', 'min:1', 'max:9999999'],
        ];
    }

    public function messages()
{
    return [
        'image.required' => '商品画像を選択してください。',
        'image.image'    => '商品画像は画像ファイルを選択してください。',
        'image.mimes'        => '画像は jpeg, png, jpg 形式でアップロードしてください。',
        'image.max'          => '画像サイズは 5MB 以下にしてください。',
        'condition.required' => '商品の状態を選択してください。',
        'name.required'        => '商品名を入力してください。',
        'description.required' => '商品の説明を入力してください。',
        'price.required' => '販売価格を入力してください。',
        'price.integer'  => '販売価格は数値で入力してください。',
    ];
}
}
