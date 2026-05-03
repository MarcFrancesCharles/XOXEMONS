<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserXuxemon extends Pivot
{
    protected $table = 'user_xuxemons';

    public $incrementing = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'xuxemon_id',
        'food_eaten',
        'disease'
    ];

    public function xuxemon()
    {
        return $this->belongsTo(Xuxemon::class, 'xuxemon_id');
    }
}