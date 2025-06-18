<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\NutritionalStatusCalculator;


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
        'z_score',
        'anthropometric_value',
        'recommendation',
    ];

    protected $casts = [
        'z_score' => 'float',
        'anthropometric_value' => 'float',
    ];

    public function getTitleAttribute()
    {
        return 'Pemeriksaan pada ' . $this->created_at->format('d M Y');
    }

    public function getCheckupDateAttribute()
    {
        if ($this->relationLoaded('checkup')) {
            return $this->checkup->checkup_date;
        }

        return null;
    }

    public function checkup()
    {
        return $this->belongsTo(Checkup::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    protected static function booted()
    {
        static::saving(function ($exam) {
            logger('Saving Examination ID: ' . $exam->id);

            if ($exam->member) {
                $result = NutritionalStatusCalculator::calculate($exam->member, $exam);
                $exam->weight_status = $result['status'];
                $exam->z_score = $result['z_score'];
                $exam->anthropometric_value = $result['anthropometric_value'];
            }
        });
    }
}
