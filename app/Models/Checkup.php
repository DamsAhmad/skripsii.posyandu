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
        'user_id',
        'status'
    ];

    protected $attributes = [
        'status' => 'active'
    ];

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
