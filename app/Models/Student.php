<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    // add fillable based on migration
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'student_number',
        'gender',
        'birth_date',
        'university_id',
        'faculty_id',
        'department_id',
        'program_id',
        'education_id',
        'year_of_admission',
        'year_of_graduation',
    ];

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    // User relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActiveIntern($query)
    {
        return $query->whereHas('applications', function ($q) {
            $q->where('status', 'diterima')
            ->whereDate('accepted_start_date', '<=', now())
            ->whereDate('accepted_end_date', '>=', now());
        });
    }
}
