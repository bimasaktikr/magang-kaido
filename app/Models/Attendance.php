<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //

    // add fillable
    protected $fillable = [
        'student_id',
        'date',
        'check_in',
        'check_in_latitude',
        'check_in_longitude',
        'check_out',
        'check_out_latitude',
        'check_out_longitude',
        'workhours',
        'status',
        'work_location',

    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
