<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    //

    // add fillable
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'description',
        'latitude',
        'longitude',
        'status',
        'start_time',
        'end_time',
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
