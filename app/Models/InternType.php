<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternType extends Model
{
    // add fillable based on migration

    // add fillable
    protected $fillable = [
        'name'
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
