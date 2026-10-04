<?php

namespace App\Http\Requests\Author;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || !$user->authorProfile) return false;

        $submission = $this->route('submission');
        if (!$submission || $submission->author_id !== $user->authorProfile->id) {
            return false;
        }

        return in_array($submission->status, ['draft', 'revision_requested']);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'synopsis' => ['required', 'string', 'min:100'],
            'proposed_price' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'manuscript_file' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'cover_preview' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'revision_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->hasFile('manuscript_file')) {
                $file = $this->file('manuscript_file');
                $handle = fopen($file->getRealPath(), 'r');
                $header = fread($handle, 5);
                fclose($handle);
                if ($header !== '%PDF-') {
                    $validator->errors()->add('manuscript_file', 'The manuscript file must be a valid PDF document (magic bytes).');
                }
            }
        });
    }
}
