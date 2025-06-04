<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checkup extends Model
{

    protected $fillable = [
        'checkup_date',
        'location',
        'annot',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
