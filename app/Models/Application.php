<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    //

    // add fillable
    protected $fillable = [
        'student_id',
        'intern_type_id',
        'req_start_date',
        'req_end_date',
        'accepted_start_date',
        'accepted_end_date',
        'status',
        'introduction_letter_path',
        'submission_letter_path',
        'cv_path',
        'hold_reason',
        'rejection_reason', // <— NEW

    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'req_start_date' => 'date',
        'req_end_date' => 'date',
        'accepted_start_date' => 'date',
        'accepted_end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->student ? $this->student->user() : null;
    }

    public function internType()
    {
        return $this->belongsTo(InternType::class, 'intern_type_id');
    }
}
