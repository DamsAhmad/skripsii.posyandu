<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = [
        'name',
        'min_age_months',
        'max_age_months',
        'for_pregnant',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
