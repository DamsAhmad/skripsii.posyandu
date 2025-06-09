<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Examination extends Model
{
    protected $fillable = [
        'member_id',
        'checkup_id',
        'weight',
        'height',
        'head_circumference',
        'abdominal_circumference',
        'arm_circumference',
        'tension',
        'uric_acid',
        'blood_sugar',
        'cholesterol',
        'gestational_week',
        'weight_status',
        'recommendation',
    ];

    public function getTitleAttribute()
    {
        return 'Pemeriksaan pada ' . $this->created_at->format('d M Y');
    }

    public function checkup()
    {
        return $this->belongsTo(Checkup::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (!$model->checkup_id) {
    //             throw new \Exception('Sesi pemeriksaan tidak valid');
    //         }
    //     });
    // }
}
