<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'image'       => ['required', 'file', 'mimes:jpeg,png', 'max:5120'],
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'condition'   => ['required', 'integer', 'in:1,2,3,4'],
            'name'        => ['required', 'string', 'max:255'],
            'brand_name'  => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'price'       => ['required', 'integer', 'min:0', 'max:9999999'],
        ];
    }

    public function messages()
    {
        return [
            'image.required' => '商品画像を選択してください。',
            'image.file'     => '商品画像はファイルを選択してください。',
            'image.mimes'    => '画像は jpeg または png 形式でアップロードしてください。',
            'image.max'      => '画像サイズは 5MB 以下にしてください。',

            'category_ids.required' => 'カテゴリーを1つ以上選択してください。',
            'category_ids.array'    => 'カテゴリーの形式が不正です。',
            'category_ids.min'      => 'カテゴリーを1つ以上選択してください。',

            'condition.required'    => '商品の状態を選択してください。',
            'name.required'         => '商品名を入力してください。',
            'description.required'  => '商品の説明を入力してください。',
            'description.max'       => '商品の説明は255文字以内で入力してください。',

            'price.required'        => '販売価格を入力してください。',
            'price.integer'         => '販売価格は数値で入力してください。',
            'price.min'             => '販売価格は0円以上で入力してください。',
        ];
    }
}
