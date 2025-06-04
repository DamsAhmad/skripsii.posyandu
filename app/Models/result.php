<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class result extends Model
{
    protected $fillable = [
        'checkup_id',
        'member_id',
        'weight',
        'height',
        'head_circum',
        'hand_circum',
        'waist_circum',
        'z_score',
        'nutrition_status',
        'notes',
    ];

    public function member()
    {
        return $this->belongsTo(\App\Models\Member::class);
    }

    public function checkup()
    {
        return $this->belongsTo(Checkup::class);
    }
}
