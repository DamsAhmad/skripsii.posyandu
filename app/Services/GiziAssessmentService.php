<?php

namespace App\Services;

use Carbon\Carbon;

class GiziAssessmentService
{
    public static function assess(string $category, array $data, string $birthdate, string $gender): array
    {
        return match ($category) {
            'balita' => self::assessBalita($data, $birthdate, $gender),
            'anak_remaja' => self::assessAnakRemaja($data, $birthdate, $gender),
            'dewasa', 'lansia' => self::assessDewasa($data),
            'ibu_hamil' => self::assessIbuHamil($data),
            default => ['status' => 'Tidak diketahui', 'z_score' => null]
        };
    }

    private static function assessBalita(array $data, string $birthdate, string $gender): array
    {
        $weight = $data['weight'];
        $height = $data['height'];
        $ageInMonths = Carbon::parse($birthdate)->diffInMonths(now());
        print($ageInMonths);

        // Tentukan jenis referensi dan kolom yang sesuai
        if ($ageInMonths <= 24) {
            $refType = 'wfl';
            $column = 'length'; // Pakai kolom 'length' untuk bayi
        } elseif ($ageInMonths <= 59) {
            $refType = 'wfh';
            $column = 'height'; // Pakai kolom 'height' untuk anak >24 bulan
        } else {
            return ['status' => 'Usia tidak sesuai untuk balita', 'z_score' => null];
        }

        // Tentukan model referensi
        $model = match (true) {
            $refType === 'wfl' && $gender === 'Laki-laki' => \App\Models\Wfl_boy::class,
            $refType === 'wfl' && $gender === 'Perempuan' => \App\Models\Wfl_girl::class,
            $refType === 'wfh' && $gender === 'Laki-laki' => \App\Models\Wfh_boy::class,
            $refType === 'wfh' && $gender === 'Perempuan' => \App\Models\Wfh_girl::class,
            default => throw new \InvalidArgumentException("Model tidak ditemukan untuk refType: $refType dan gender: $gender"),
        };

        // Gunakan kolom yang sudah ditentukan di atas
        $roundedValue = number_format($height, 1, '.', '');
        $standard = $model::where($column, $roundedValue)->first();

        if (!$standard) {
            return ['status' => 'Data standar tidak ditemukan', 'z_score' => null];
        }

        $zScore = self::calculateZScore($weight, $standard->L, $standard->M, $standard->S);
        $status = self::interpretZScore($zScore, $refType);
        print($zScore);
        print($status);
        return [
            'status' => $status,
            'z_score' => round($zScore, 2),
            'category' => strtoupper($refType)
        ];
    }

    // $heightColumn = ($refType === 'wfl') ? 'length' : 'height';

    //         // Pastikan pembulatan 1 angka desimal
    //         $roundedHeight = number_format($height, 1, '.', '');

    //         // Ambil data standar dari model referensi
    //         $standard = $model::where($heightColumn, $roundedHeight)->first();

    //         // Ambil referensi dari tabel berdasarkan tinggi (dibulatkan ke 0.1)
    //         // $standard = $model::where('height', round($height, 1))->first();
    //         // $standard = $model::where($column, round($height, 1))->first();

    //         if (!$standard) {
    //             return ['status' => 'Data standar tidak ditemukan', 'z_score' => null];
    //         }

    //         // Hitung Z-Score pakai rumus WHO LMS
    //         $zScore = self::calculateZScore($weight, $standard->L, $standard->M, $standard->S);
    //         logger()->info("ZScore calculated", ['zScore' => $zScore]);
    //         $status = self::interpretZScore($zScore, $refType);
    //         print($zScore);

    //         return [
    //             'status' => $status,
    //             'z_score' => round($zScore, 2),
    //             'category' => strtoupper($refType)
    //         ];

    //         // debug
    //         logger()->info("AssessBalita input", compact('weight', 'height', 'ageInMonths', 'gender'));
    //     }

    private static function assessAnakRemaja(array $data, string $birthdate, string $gender): array
    {
        $ageInMonths = Carbon::parse($birthdate)->diffInMonths(now());
        $bmi = $data['weight'] / pow($data['height'] / 100, 2);

        $model = ($gender === 'L') ? \App\Models\Bfa_boy::class : \App\Models\Bfa_girl::class;
        $standard = $model::where('age_months', $ageInMonths)->first();

        if (!$standard) {
            return ['status' => 'Data standar tidak ditemukan', 'z_score' => null];
        }

        $zScore = self::calculateZScore($bmi, $standard->L, $standard->M, $standard->S);
        $status = self::interpretZScore($zScore, 'bfa');

        return [
            'status' => $status,
            'z_score' => round($zScore, 2),
            'bmi' => round($bmi, 2)
        ];
    }

    private static function assessDewasa(array $data): array
    {
        $bmi = $data['weight'] / pow($data['height'] / 100, 2);

        $status = match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25   => 'Normal',
            $bmi < 30   => 'Overweight',
            default     => 'Obesitas',
        };

        return [
            'bmi' => round($bmi, 2),
            'status' => $status,
        ];
    }

    private static function assessIbuHamil(array $data): array
    {
        $status = ($data['hand_circum'] < 23.5)
            ? 'Risiko KEK (Kurang Energi Kronis)'
            : 'Normal';

        return [
            'hand_circum' => $data['hand_circum'],
            'status' => $status,
        ];
    }

    private static function calculateZScore(float $x, float $l, float $m, float $s): float
    {
        return $l != 0
            ? (pow(($x / $m), $l) - 1) / ($l * $s)
            : log($x / $m) / $s;
    }

    private static function interpretZScore(float $zScore, string $type): string
    {
        // Kriteria WHO umum
        return match (true) {
            $zScore < -3 => 'Gizi Buruk (Severe)',
            $zScore < -2 => 'Gizi Kurang',
            $zScore <= 1 => 'Normal',
            $zScore <= 2 => 'Berisiko Overweight',
            default => 'Obesitas',
        };
    }
}
