<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    // add fillable based on migration
    protected $fillable = [
        'name',
        'slug',
        // add other columns from the departments table migration as needed
    ];

    // add fillable
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
