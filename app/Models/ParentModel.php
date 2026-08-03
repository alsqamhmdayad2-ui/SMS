<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'guardian_type',
        'full_name',
        'first_name',
        'father_name',
        'grandfather_name',
        'family_name',
        'national_id',
        'email',
        'phone',
        'phone_1',
        'phone_2',
        'occupation',
        'workplace',
        'address',
    ];

    /**
     * Get the display name: use 4-part if available, otherwise return stored full_name.
     */
    public function getFullNameAttribute($value): string
    {
        if ($this->first_name && $this->father_name && $this->grandfather_name && $this->family_name) {
            return trim("{$this->first_name} {$this->father_name} {$this->grandfather_name} {$this->family_name}");
        }
        return $value ?? '';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
