<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'nik',
        'no_kk',
        'member_name',
        'gender',
        'birthdate',
        'birthplace',
        'category',
        'father',
        'mother',
        'parent_phone',
        'is_pregnant',
        'category_id'
    ];

    protected $casts = [
        'birthdate' => 'datetime',
    ];

    // public function results()
    // {
    //     return $this->hasMany(\App\Models\Result::class);
    // }

    public function examinations()
    {
        return $this->hasMany(Examination::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
