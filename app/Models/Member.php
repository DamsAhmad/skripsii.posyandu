<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_name',
        'gender',
        'birthdate',
        'birthplace',
        'category',
        'is_pregnant'
    ];

    public function results()
    {
        return $this->hasMany(\App\Models\Result::class);
    }
}
