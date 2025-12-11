<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'body' => [
                'required',
                'string',
                'max:255',
                function ($attr, $value, $fail) {
                    if (is_string($value) && trim($value) === '') {
                        $fail('コメントを入力してください。');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'コメントを入力してください。',
            'body.max'      => 'コメントは255文字以内で入力してください。',
        ];
    }

    //空白のみの投稿を防ぐ
    protected function prepareForValidation(): void{
        if (is_string($this->body)) {
            $this->merge(['body' => trim($this->body)]);
        }
    }

}
