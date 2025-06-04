<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrowthReference extends Model
{
    public static function getReference($indicator, $ageMonths, $gender)
    {
        // Cari tepat
        $reference = self::where('indicator', $indicator)
            ->where('gender', $gender)
            ->where('age_months', $ageMonths)
            ->first();

        if ($reference) {
            return $reference;
        }

        // Fallback: cari yang terdekat (±1 bulan)
        return self::where('indicator', $indicator)
            ->where('gender', $gender)
            ->whereBetween('age_months', [$ageMonths - 1, $ageMonths + 1])
            ->orderByRaw('ABS(age_months - ?)', [$ageMonths])
            ->first();
    }
}
