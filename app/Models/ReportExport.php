<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'report_type',
        'format',
        'filters',
        'status',
        'file_name',
        'ip_address',
        'user_agent',
        'duration_ms',
        'exported_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'exported_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }
}
