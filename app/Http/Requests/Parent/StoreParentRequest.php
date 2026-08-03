<?php

namespace App\Http\Requests\Parent;

use Illuminate\Foundation\Http\FormRequest;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'     => 'required|string|max:255',
            'father_name'    => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'family_name'    => 'required|string|max:255',
            'guardian_type'  => 'required|in:Father,Mother,Guardian',
            'national_id'    => 'required|string|max:20|unique:parents,national_id',
            'phone_1'        => 'required|string|max:20',
            'phone_2'        => 'nullable|string|max:20',
            'occupation'     => 'nullable|string|max:255',
            'workplace'      => 'nullable|string|max:255',
            'address'        => 'nullable|string',
        ];
    }
}
