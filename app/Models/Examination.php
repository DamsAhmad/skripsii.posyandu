<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Examination extends Model
{
    protected $fillable = [
        'checkup_id',
        'member_id',
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

    public function checkups()
    {
        return $this->belongsTo(Checkup::class);
    }

    public function members()
    {
        return $this->belongsTo(Member::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->checkup_id) {
                throw new \Exception('Sesi pemeriksaan tidak valid');
            }
        });
    }
}
