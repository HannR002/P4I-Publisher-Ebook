<?php

namespace App\Http\Requests\Author;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterAuthorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::check() && !\Illuminate\Support\Facades\Auth::user()->isAuthor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'pen_name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];

        if (! config('features.author_kyc')) {
            return $rules;
        }

        return $rules + [
            'id_card_number' => ['required', 'string', 'regex:/^[0-9]{16}$/'],
            'id_card_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
            'bank_name' => ['required', 'string', 'max:50'],
            'bank_account' => ['required', 'string', 'max:50'],
            'bank_holder_name' => ['required', 'string', 'max:150'],
        ];
    }
}
