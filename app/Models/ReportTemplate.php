<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'type',
        'language',
        'font_family',
        'show_logo',
        'show_signature',
        'show_qr',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'header',
        'footer',
        'watermark',
        'orientation',
        'paper_size',
        'is_default',
        'status',
        'version',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_signature' => 'boolean',
        'show_qr' => 'boolean',
        'is_default' => 'boolean',
    ];
}
