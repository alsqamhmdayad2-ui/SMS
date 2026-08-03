<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_code',
        'school_name',
        'school_short_name',
        'school_name_en',
        'logo',
        'academic_logo',
        'address',
        'phone',
        'email',
        'website',
        'principal_name',
        'principal_signature',
        'report_footer',
        'country',
        'city',
        'postal_code',
        'timezone',
        'currency',
        'academic_system',
        'grading_system',
    ];
}
