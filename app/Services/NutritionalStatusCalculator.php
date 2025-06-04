<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Examination;
use App\Models\GrowthReference;


class NutritionalStatusCalculator
{
    public static function calculate(Member $member, Examination $exam)
    {
        $ageInMonths = $member->birthdate->diffInMonths(now());

        // Handle khusus ibu hamil (IMT + LiLA)
        if ($member->is_pregnant) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            $lila = $exam->arm_circumference; // Lingkar Lengan Atas

            return self::calculatePregnantStatus(
                $imt,
                $lila,
                $exam->gestational_week
            );
        }

        // Kategori lainnya
        if ($member->category === 'balita') {
            return self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
        }

        if ($member->category === 'anak-remaja') {
            return self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
        }

        if (in_array($member->category, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            return self::categorizeIMT($imt, $member->category);
        }

        return 'Kategori tidak didukung';
    }

    private static function calculateWeightForAge($ageMonths, $weight, $gender)
    {
        // Ambil data referensi
        $reference = GrowthReference::getReference('bbu', $ageMonths, $gender);

        if (!$reference) {
            return 'Data referensi tidak tersedia';
        }

        $median = $reference->median;
        $sd_minus = $reference->sd_minus; // -1 SD
        $sd_plus = $reference->sd_plus;   // +1 SD (tambahkan kolom ini di tabel)

        // Rumus Z-score berdasarkan posisi relatif terhadap median
        if ($weight < $median) {
            $z_score = ($weight - $median) / ($median - $sd_minus);
        } else {
            $z_score = ($weight - $median) / ($sd_plus - $median);
        }

        // Klasifikasi berdasarkan Permenkes
        if ($z_score < -3) return 'Gizi Buruk';
        if ($z_score < -2) return 'Gizi Kurang';
        if ($z_score <= 1) return 'Normal';
        if ($z_score <= 2) return 'Beresiko Gizi Lebih';
        if ($z_score <= 3) return 'Gizi Lebih';
        return 'Obesitas';
    }

    private static function calculateIMTForAge($ageMonths, $weight, $height, $gender)
    {
        $heightInMeters = $height / 100;
        $actualIMT = $weight / ($heightInMeters * $heightInMeters);

        $reference = GrowthReference::getReference('imtu', $ageMonths, $gender);

        if (!$reference) {
            return 'Data referensi tidak tersedia';
        }

        $median = $reference->median;
        $sd_minus = $reference->sd_minus; // -1 SD
        $sd_plus = $reference->sd_plus;   // +1 SD (tambahkan kolom ini di tabel)

        // Rumus Z-score berdasarkan posisi relatif terhadap median
        if ($actualIMT < $median) {
            $z_score = ($actualIMT - $median) / ($median - $sd_minus);
        } else {
            $z_score = ($actualIMT - $median) / ($sd_plus - $median);
        }

        // Klasifikasi
        if ($z_score < -3) return 'Sangat Kurus';
        if ($z_score < -2) return 'Kurus';
        if ($z_score <= 1) return 'Normal';
        if ($z_score <= 2) return 'Beresiko Lebih';
        if ($z_score <= 3) return 'Lebih';
        return 'Obesitas';
    }

    private static function categorizeIMT($imt, $category)
    {
        // Standar Kemenkes untuk dewasa dan lansia
        if ($category === 'dewasa' || $category === 'lansia') {
            if ($imt < 18.5) {
                return 'Kurus';
            } elseif ($imt >= 18.5 && $imt < 25.0) {
                return 'Normal';
            } elseif ($imt >= 25.0 && $imt < 30.0) {
                return 'Gemuk';
            } else {
                return 'Obesitas';
            }
        }

        return 'Kategori tidak valid';
    }

    private static function calculatePregnantStatus($imt, $lila, $gestationalWeek = null)
    {
        // Kategori berdasarkan IMT pra-kehamilan
        $imtCategory = self::categorizePregnantIMT($imt);

        // Evaluasi LiLA (KEK - Kurang Energi Kronis)
        $kekStatus = ($lila < 23.5) ? 'KEK' : 'Normal';

        // Gabungkan hasil
        return self::combinePregnantStatus($imtCategory, $kekStatus, $gestationalWeek);
    }

    private static function categorizePregnantIMT($imt)
    {
        if ($imt < 18.5) return 'Kurus';
        if ($imt < 25.0) return 'Normal';
        if ($imt < 30.0) return 'Gemuk';
        return 'Obesitas';
    }

    private static function combinePregnantStatus($imtCategory, $kekStatus, $gestationalWeek)
    {
        $trimesterSuffix = $gestationalWeek ? ' Trimester ' . ceil($gestationalWeek / 13) : '';

        if ($kekStatus === 'KEK') {
            return $imtCategory . ' + KEK' . $trimesterSuffix;
        }

        return $imtCategory . $trimesterSuffix;
    }
}
