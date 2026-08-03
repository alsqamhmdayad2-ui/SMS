<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'code'        => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('subjects')->ignore($this->route('subject')),
            ],
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            // class_ids: array of class IDs to link (optional, managed via pivot)
            'class_ids'   => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ];
    }
}
