<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Xuxemon extends Model
{
    protected $fillable = [
        'name',
        'type',
        'size',
        'image',
    ];

    protected $casts = [
        'type' => 'string',
        'size' => 'string',
    ];
}