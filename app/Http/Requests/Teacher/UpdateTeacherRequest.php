<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'       => 'required|string|max:255',
            'father_name'      => 'required|string|max:255',
            'grandfather_name' => 'nullable|string|max:255',
            'family_name'      => 'required|string|max:255',
            'national_id'      => 'required|string|max:20|unique:teachers,national_id,' . $this->teacher->id,
            'phone'            => 'nullable|string|max:20',
            'specialization'   => 'nullable|string|max:255',
            'salary'           => 'nullable|numeric',
            'address'          => 'nullable|string',
            'subjects'         => 'nullable|array',
            'subjects.*'       => 'exists:subjects,id',
        ];
    }
}
