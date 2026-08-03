<?php

namespace App\Http\Requests\SchoolClass;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => 'required|exists:grades,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes')->where('grade_id', $this->grade_id)->where('academic_year_id', $this->academic_year_id)->ignore($this->route('class')),
            ],
            'description' => 'nullable|string',
            'status' => 'boolean',
        ];
    }
}
