<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'type' => 'required|string|in:mcq,true_false,short_answer,essay,matching,fill_blank',
            'question_text' => 'required|string',
            'mark' => 'numeric|min:0.5',
            'difficulty' => 'string|in:easy,medium,hard',
            'bloom_level' => 'nullable|string|in:remember,understand,apply,analyze,evaluate,create',
            'estimated_time' => 'nullable|integer|min:1',
            'is_public' => 'nullable|boolean',
        ];

        $type = $this->input('type');

        if ($type === 'mcq') {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string';
            $rules['correct_option_index'] = 'required|integer|min:0';
        } elseif ($type === 'true_false') {
            $rules['is_correct_boolean'] = 'required|boolean';
        } elseif ($type === 'matching') {
            $rules['pairs'] = 'required|array|min:2';
            $rules['pairs.*.left'] = 'required|string';
            $rules['pairs.*.right'] = 'required|string';
            $rules['pairs.*.partial_mark'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }
}
