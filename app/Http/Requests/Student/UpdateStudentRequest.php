<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            // Personal
            'first_name'       => 'required|string|max:255',
            'father_name'      => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'family_name'      => 'required|string|max:255',
            'english_name'     => 'nullable|string|max:255',
            'national_id'      => 'required|string|max:20|unique:students,national_id,' . $studentId,
            'phone'            => 'nullable|string|max:20',
            'birth_date'       => 'required|date',
            'place_of_birth'   => 'nullable|string|max:255',
            'gender'           => 'required|in:Male,Female',
            'nationality'      => 'required|string',
            'blood_type'       => 'nullable|string',
            'religion'         => 'nullable|string',
            'health_status'    => 'nullable|string',
            'avatar'           => 'nullable|image|max:2048',

            // Parent
            'parent_id'         => 'nullable|exists:parents,id',
            'parent_full_name'  => 'required_without:parent_id|nullable|string',
            'parent_national_id'=> 'required_without:parent_id|nullable|string',
            'guardian_type'     => 'required_without:parent_id|nullable|string',
            'parent_phone_1'    => 'required_without:parent_id|nullable|string',
            'parent_phone_2'    => 'nullable|string',
            'parent_occupation' => 'nullable|string',
            'parent_workplace'  => 'nullable|string',

            // Address
            'governorate'      => 'required|string',
            'city'             => 'required|string',
            'region'           => 'nullable|string',
            'neighborhood'     => 'nullable|string',
            'street'           => 'nullable|string',
            'nearest_landmark' => 'nullable|string',

            // Academic
            'stage_id'          => 'required|exists:grades,id',
            'grade_id'          => 'required|exists:classes,id',
            'section_id'        => 'required|exists:sections,id',
        ];
    }
}
