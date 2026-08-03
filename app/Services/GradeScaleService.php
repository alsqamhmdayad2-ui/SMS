<?php

namespace App\Services;

use App\Models\GradeScale;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class GradeScaleService
{
    /**
     * Store a new Grade Scale, ensuring no overlaps.
     */
    public function storeScale(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->validateNoOverlap($data['percentage_from'], $data['percentage_to']);
            return GradeScale::create($data);
        });
    }

    /**
     * Update an existing Grade Scale, ensuring no overlaps.
     */
    public function updateScale(GradeScale $scale, array $data)
    {
        return DB::transaction(function () use ($scale, $data) {
            $from = $data['percentage_from'] ?? $scale->percentage_from;
            $to = $data['percentage_to'] ?? $scale->percentage_to;
            
            $this->validateNoOverlap($from, $to, $scale->id);
            
            $scale->update($data);
            return $scale;
        });
    }

    /**
     * Prevent overlapping grade scale ranges.
     */
    protected function validateNoOverlap($from, $to, $ignoreId = null)
    {
        if ($from > $to) {
            throw ValidationException::withMessages([
                'percentage_from' => 'Starting percentage cannot be greater than ending percentage.',
            ]);
        }

        $query = GradeScale::where(function ($q) use ($from, $to) {
            $q->whereBetween('percentage_from', [$from, $to])
              ->orWhereBetween('percentage_to', [$from, $to])
              ->orWhere(function ($q2) use ($from, $to) {
                  $q2->where('percentage_from', '<=', $from)
                     ->where('percentage_to', '>=', $to);
              });
        });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'percentage_from' => 'The provided percentage range overlaps with an existing Grade Scale.',
            ]);
        }
    }
}
