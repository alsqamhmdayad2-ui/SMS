<?php

namespace App\Services;

use App\Models\AssessmentComponent;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AssessmentService
{
    /**
     * Get all components for a specific subject in an academic year.
     */
    public function getSubjectComponents($subjectId, $academicYearId)
    {
        return AssessmentComponent::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Store a new assessment component, ensuring the total weight doesn't exceed 100%.
     */
    public function storeComponent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->validateWeightLimit($data['subject_id'], $data['academic_year_id'], $data['weight_percentage']);
            
            return AssessmentComponent::create($data);
        });
    }

    /**
     * Update an existing component, ensuring the total weight doesn't exceed 100%.
     */
    public function updateComponent(AssessmentComponent $component, array $data)
    {
        return DB::transaction(function () use ($component, $data) {
            if (isset($data['weight_percentage']) && $data['weight_percentage'] != $component->weight_percentage) {
                $this->validateWeightLimit(
                    $component->subject_id, 
                    $component->academic_year_id, 
                    $data['weight_percentage'], 
                    $component->id
                );
            }
            
            $component->update($data);
            return $component;
        });
    }

    /**
     * Ensure that the sum of all components for a subject does not exceed 100%.
     * Throws ValidationException if it does.
     */
    protected function validateWeightLimit($subjectId, $academicYearId, $newWeight, $ignoreComponentId = null)
    {
        $query = AssessmentComponent::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId);
            
        if ($ignoreComponentId) {
            $query->where('id', '!=', $ignoreComponentId);
        }
        
        $currentTotal = $query->sum('weight_percentage');
        
        if (($currentTotal + $newWeight) > 100) {
            throw ValidationException::withMessages([
                'weight_percentage' => 'Total weight for this subject cannot exceed 100%. Current total: ' . $currentTotal . '%',
            ]);
        }
    }

    /**
     * Check if the total weight of all components for a subject is exactly 100%.
     */
    public function isWeightComplete($subjectId, $academicYearId): bool
    {
        $totalWeight = AssessmentComponent::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->sum('weight_percentage');

        return $totalWeight == 100;
    }
}
