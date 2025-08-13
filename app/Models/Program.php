<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    // add fillable based on migration
    protected $fillable = [
        'name',
        'slug',
        'degree',
        'faculty_id',
        'department_id',
    ];

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
