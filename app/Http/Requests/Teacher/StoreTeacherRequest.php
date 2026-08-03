<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'nullable|string|max:255',
            'family_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:teachers,national_id',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'salary' => 'nullable|numeric',
            'max_weekly_periods' => 'required|integer|min:0|max:40',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
