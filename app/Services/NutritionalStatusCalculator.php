<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Examination;
use App\Models\GrowthReference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class NutritionalStatusCalculator
{
    public static function calculate(Member $member, Examination $exam)
    {

        $checkupDate = self::getCheckupDate($exam);
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        Log::debug('Age in months: ' . $ageInMonths);
        if (!$exam->weight || !$exam->height) {
            return [
                'status' => 'Data tidak lengkap',
                'z_score' => null,
                'anthropometric_value' => null
            ];
        }

        if ($member->is_pregnant) {
            $heightInMeters = $exam->height / 100;
            // $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            $lila = $exam->arm_circumference;

            return self::calculatePregnantStatus($lila, $exam->gestational_week);
        }

        if ($member->category === 'balita') {
            return self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
        }

        if ($member->category === 'anak-remaja') {
            return self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
        }

        if (in_array($member->category, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);

            return [
                'status' => self::categorizeIMT($imt, $member->category),
                'z_score' => null,
                'anthropometric_value' => $imt
            ];
        }

        return [
            'status' => self::generateStatus($member, $exam),
            'z_score' => self::generateZscore($member, $exam),
            'anthropometric_value' => self::generateAnthropometric($member, $exam),
        ];
    }

    public static function generateStatus(Member $member, Examination $exam): string
    {

        $checkupDate = self::getCheckupDate($exam);

        if (!$exam->weight || !$exam->height) {
            return 'Data tidak lengkap';
        }

        if ($member->is_pregnant) {
            $lila = $exam->arm_circumference;

            if (is_null($lila)) {
                return 'LiLA tidak tersedia';
            }

            return $lila < 23.5 ? 'KEK' : 'Normal';
        }

        if ($member->category === 'balita') {
            $ageInMonths = $member->birthdate->diffInMonths($checkupDate);
            $result = self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
            return $result['status'];
        }

        if ($member->category === 'anak-remaja') {
            $ageInMonths = $member->birthdate->diffInMonths($checkupDate);
            $result = self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
            return $result['status'];
        }


        if (in_array($member->category, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            return self::categorizeIMT($imt, $member->category);
        }

        return 'Kategori tidak didukung';
    }

    public static function generateZscore(Member $member, Examination $exam): ?float
    {
        $checkupDate = self::getCheckupDate($exam);
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        if (!$exam->weight || !$exam->height) return null;
        if ($member->is_pregnant || in_array($member->category, ['dewasa', 'lansia'])) return null;

        if ($member->category === 'balita') {
            $result = self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
            return $result['z_score'];
        }

        if ($member->category === 'anak-remaja') {
            $result = self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
            return $result['z_score'];
        }

        return null;
    }

    private static function getCheckupDate(Examination $exam): Carbon
    {
        if ($exam->relationLoaded('checkup') && $exam->checkup) {
            return Carbon::parse($exam->checkup->checkup_date);
        }

        if ($exam->checkup_date) {
            return Carbon::parse($exam->checkup_date);
        }

        return now();
    }

    public static function generateAnthropometric(Member $member, Examination $exam): ?float
    {
        if ($member->is_pregnant) {
            return $exam->arm_circumference ?? null;
        }

        if (!$exam->weight || !$exam->height) return null;

        if (in_array($member->category, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            return $exam->weight / ($heightInMeters * $heightInMeters);
        }

        $checkupDate = self::getCheckupDate($exam);
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        if ($member->category === 'balita') {
            return (float) $exam->weight;
        }

        if ($member->category === 'anak-remaja') {
            $heightInMeters = $exam->height / 100;
            return $exam->weight / ($heightInMeters * $heightInMeters);
        }

        return null;
    }

    private static function calculateWeightForAge($ageMonths, $weight, $gender)
    {
        $reference = GrowthReference::getReference('bbu', $ageMonths, $gender);

        if (!$reference) {
            return [
                'status' => 'Data referensi tidak tersedia',
                'z_score' => null,
                'anthropometric_value' => (float) $weight
            ];
        }

        $median = $reference->median;
        $sd_minus = $reference->sd_minus;
        $sd_plus = $reference->sd_plus;

        if ($weight < $median) {
            $z_score = ($weight - $median) / ($median - $sd_minus);
        } else {
            $z_score = ($weight - $median) / ($sd_plus - $median);
        }

        if ($z_score < -3) {
            $status = 'BB sangat kurang';
        } elseif ($z_score < -2) {
            $status = 'BB kurang';
        } elseif ($z_score <= 1) {
            $status = 'BB normal';
        } else {
            $status = 'Risiko BB lebih';
        }



        Log::info([
            'age' => $ageMonths,
            'gender' => $gender,
            'weight' => $weight,
            'median' => $median,
            'sd_minus' => $sd_minus,
            'sd_plus' => $sd_plus,
            'z_score' => $z_score,
        ]);


        return [
            'status' => $status,
            'z_score' => round($z_score, 1),
            'anthropometric_value' => round((float) $weight, 1)
        ];
    }

    private static function calculateIMTForAge($ageMonths, $weight, $height, $gender)
    {
        $heightInMeters = $height / 100;
        $actualIMT = $weight / ($heightInMeters * $heightInMeters);

        $reference = GrowthReference::getReference('imtu', $ageMonths, $gender);

        if (!$reference) {
            return [
                'status' => 'Data referensi tidak tersedia',
                'z_score' => null,
                'anthropometric_value' => round($actualIMT, 1),
            ];
        }

        // Bangun array kurva SD
        $curve = [
            "-3" => $reference->sd_minus_3,
            "-2" => $reference->sd_minus_2,
            "0"  => $reference->median,
            "+1" => $reference->sd_plus_1,
            "+2" => $reference->sd_plus_2,
            "+3" => $reference->sd_plus_3,
        ];

        $z_score = null;

        if ($actualIMT < $curve["0"]) {
            if ($actualIMT < $curve["-3"]) {
                $denom = $curve["-2"] - $curve["-3"];
                $z_score = $denom != 0
                    ? -3 + (($actualIMT - $curve["-3"]) / $denom)
                    : -3;
            } elseif ($actualIMT < $curve["-2"]) {
                $z_score = -2 + (($actualIMT - $curve["-2"]) / ($curve["-2"] - $curve["-3"]));
            } elseif ($actualIMT < $curve["0"]) {
                $z_score = ($actualIMT - $curve["0"]) / ($curve["0"] - $curve["-2"]) * 2;
            }
        } else {
            if ($actualIMT > $curve["+3"]) {
                $denom = $curve["+3"] - $curve["+2"];
                $z_score = $denom != 0
                    ? 3 + (($actualIMT - $curve["+3"]) / $denom)
                    : 3;
            } elseif ($actualIMT > $curve["+2"]) {
                $z_score = 2 + (($actualIMT - $curve["+2"]) / ($curve["+2"] - $curve["+1"]));
            } elseif ($actualIMT > $curve["0"]) {
                $z_score = ($actualIMT - $curve["0"]) / ($curve["+2"] - $curve["0"]) * 2;
            } else {
                $z_score = 0;
            }
        }

        // Kategori status gizi berdasarkan Z-score Permenkes
        if ($z_score < -3) {
            $status = 'Gizi Buruk';
        } elseif ($z_score >= -3 && $z_score < -2) {
            $status = 'Gizi Kurang';
        } elseif ($z_score >= -2 && $z_score <= 1) {
            $status = 'Normal';
        } elseif ($z_score > 1 && $z_score <= 2) {
            $status = 'Gizi Lebih';
        } else {
            $status = 'Obesitas';
        }

        return [
            'status' => $status,
            'z_score' => round($z_score, 1),
            'anthropometric_value' => round($actualIMT, 1),
        ];
    }



    public static function calculatePregnantStatus($lila, $gestationalWeek = null)
    {
        // $imtCategory = self::categorizePregnantIMT($imt);
        $kekStatus = ($lila < 23.5) ? 'KEK' : 'Normal';


        return [
            'status' => $kekStatus,
            'z_score' => null,
            'anthropometric_value' => $lila
        ];
    }

    // private static function combinePregnantStatus($imtCategory, $kekStatus, $gestationalWeek)
    // {
    //     $trimesterSuffix = $gestationalWeek ? ' Trimester ' . ceil($gestationalWeek / 13) : '';

    //     if ($kekStatus === 'KEK') {
    //         return $imtCategory . ' + KEK' . $trimesterSuffix;
    //     }

    //     return $imtCategory . $trimesterSuffix;
    // }


    // private static function categorizePregnantIMT($imt)
    // {
    //     if ($imt < 18.5) return 'Kurus';
    //     if ($imt < 25.0) return 'Normal';
    //     if ($imt < 30.0) return 'Gemuk (Pra-Obesitas)';
    //     if ($imt < 35.0) return 'Obesitas Kelas I';
    //     if ($imt < 40.0) return 'Obesitas Kelas II';
    //     if ($imt >= 40.0) return 'Obesitas Kelas III';
    //     return 'Obesitas';
    // }

    private static function categorizeIMT($imt, $category)
    {
        if ($category === 'dewasa') {
            if ($imt < 18.5) {
                return 'Kurus';
            } elseif ($imt < 25.0) {
                return 'Normal';
            } elseif ($imt < 30.0) {
                return 'Gemuk (Pra-Obesitas)';
            } elseif ($imt < 35.0) {
                return 'Obesitas Kelas I';
            } elseif ($imt < 40.0) {
                return 'Obesitas Kelas II';
            } else {
                return 'Obesitas Kelas III';
            }
        } elseif ($category === 'lansia') {
            if ($imt < 22.0) {
                return 'Kurus (Risiko Malnutrisi) (IMT: ' . round($imt, 1) . ')';
            } elseif ($imt < 27.0) {
                return 'Normal (IMT: ' . round($imt, 1) . ')';
            } else {
                return 'Gemuk/Obesitas (IMT: ' . round($imt, 1) . ')';
            }
        }

        return 'Kategori tidak valid';
    }

    public static function generateRecommendation(Examination $exam)
    {
        $member = $exam->member;
        $status = $exam->weight_status;
        $category = $member->category;
        $isPregnant = $member->is_pregnant;

        $recommendation = "Hasil pemeriksaan: $status. ";

        if ($isPregnant) {
            $lila = $exam->arm_circumference;
            $gestationalWeek = $exam->gestational_week;
            $trimester = ceil($gestationalWeek / 13);

            $recommendation .= "Kehamilan minggu ke-$gestationalWeek (Trimester $trimester). ";

            if (strpos($status, 'KEK') !== false) {
                $recommendation .= "Lingkar Lengan Atas (LiLA) $lila cm (< 23.5 cm) menunjukkan risiko KEK. ";
                $recommendation .= "Perbanyak konsumsi makanan tinggi protein dan energi seperti telur, ikan, daging, kacang-kacangan. ";
                $recommendation .= "Anjurkan suplementasi zat besi dan asam folat. ";
            }

            if (strpos($status, 'Kurus') !== false) {
                $recommendation .= "Penambahan berat badan dianjurkan 12.5-18 kg selama kehamilan. ";
                $recommendation .= "Konsumsi tambahan 340-450 kkal/hari dengan fokus pada protein. ";
            } elseif (strpos($status, 'Normal') !== false) {
                $recommendation .= "Penambahan berat badan dianjurkan 11.5-16 kg selama kehamilan. ";
                $recommendation .= "Pertahankan pola makan seimbang dengan tambahan 300 kkal/hari. ";
            } elseif (strpos($status, 'Gemuk') !== false) {
                $recommendation .= "Penambahan berat badan dianjurkan 7-11.5 kg selama kehamilan. ";
                $recommendation .= "Batasi gula dan lemak jenuh, perbanyak sayuran dan buah. ";
            } elseif (strpos($status, 'Obesitas') !== false) {
                $recommendation .= "Penambahan berat badan dianjurkan 5-9 kg selama kehamilan. ";
                $recommendation .= "Pantau ketat gula darah dan tekanan darah. ";
            }

            $recommendation .= "Lakukan kontrol rutin: ";
            if ($gestationalWeek < 28) {
                $recommendation .= "setiap 4 minggu. ";
            } elseif ($gestationalWeek < 36) {
                $recommendation .= "setiap 2 minggu. ";
            } else {
                $recommendation .= "setiap minggu. ";
            }

            $recommendation .= "Hindari rokok dan alkohol. ";
        } elseif ($category === 'balita') {
            if (strpos($status, 'Gizi Buruk') !== false) {
                $recommendation .= "Segera rujuk ke Puskesmas! Berikan makanan tinggi energi dan protein. ";
                $recommendation .= "Pantau berat badan setiap minggu. Berikan susu terapi gizi. ";
            } elseif (strpos($status, 'Gizi Kurang') !== false) {
                $recommendation .= "Tingkatkan frekuensi makan menjadi 5-6 kali sehari. ";
                $recommendation .= "Berikan makanan padat energi seperti bubur kacang hijau, pisang, dan telur. ";
                $recommendation .= "Pantau berat badan setiap bulan. ";
            } elseif (strpos($status, 'Normal') !== false) {
                $recommendation .= "Pertahankan pola makan seimbang. Lanjutkan ASI eksklusif jika usia < 6 bulan. ";
                $recommendation .= "Berikan MP-ASI sesuai usia. Lakukan stimulasi tumbuh kembang. ";
            } elseif (strpos($status, 'Beresiko Gizi Lebih') !== false) {
                $recommendation .= "Batasi makanan tinggi gula dan lemak. Perbanyak aktivitas fisik. ";
                $recommendation .= "Berikan buah sebagai camilan. Pantau lingkar lengan setiap bulan. ";
            } elseif (strpos($status, 'Gizi Lebih') !== false || strpos($status, 'Obesitas') !== false) {
                $recommendation .= "Konsultasikan ke ahli gizi. Atur pola makan seimbang. ";
                $recommendation .= "Batasi susu formula, perbanyak aktivitas fisik. Pantau berat badan setiap bulan. ";
            }

            $ageInMonths = $member->birthdate->diffInMonths(now());
            if ($ageInMonths < 24) {
                $recommendation .= "Pastikan imunisasi lengkap sesuai usia. ";
            }
        } elseif ($category === 'anak-remaja') {
            if (strpos($status, 'Kurus') !== false) {
                $recommendation .= "Tingkatkan asupan kalori dan protein. Makan 3 kali utama + 2-3 kali selingan. ";
                $recommendation .= "Pilih makanan padat gizi seperti susu, telur, daging, dan kacang-kacangan. ";
            } elseif (strpos($status, 'Normal') !== false) {
                $recommendation .= "Pertahankan pola makan gizi seimbang. Lakukan aktivitas fisik 60 menit/hari. ";
                $recommendation .= "Batasi screen time maksimal 2 jam/hari. ";
            } else {
                $recommendation .= "Batasi makanan cepat saji dan minuman manis. Perbanyak sayur dan buah. ";
                $recommendation .= "Tingkatkan aktivitas fisik minimal 60 menit/hari. Pantau berat badan bulanan. ";
            }

            $recommendation .= "Edukasi pentingnya sarapan dan pola makan teratur. ";
        } elseif ($category === 'dewasa' || $category === 'lansia') {
            if (strpos($status, 'Kurus') !== false) {
                $recommendation .= "Tingkatkan asupan kalori dengan makanan padat gizi. ";
                $recommendation .= "Konsumsi suplemen jika diperlukan. Pantau kemungkinan penyakit kronis. ";
            } elseif (strpos($status, 'Gemuk') !== false || strpos($status, 'Obesitas') !== false) {
                $recommendation .= "Turunkan berat badan secara bertahap (0.5-1 kg/minggu). ";
                $recommendation .= "Kurangi porsi makan, perbanyak sayur dan buah. Olahraga 30 menit/hari. ";
            } else {
                $recommendation .= "Pertahankan pola makan gizi seimbang. Lakukan aktivitas fisik teratur. ";
            }

            if ($exam->tension) {
                [$systolic, $diastolic] = explode('/', $exam->tension);
                if ($systolic >= 140 || $diastolic >= 90) {
                    $recommendation .= "Tekanan darah tinggi! Anjurkan kontrol ke Puskesmas. ";
                    $recommendation .= "Batasi garam maksimal 1 sendok teh/hari. ";
                }
            }

            if ($exam->blood_sugar) {
                if ($exam->blood_sugar > 200) {
                    $recommendation .= "Gula darah sangat tinggi (>200 mg/dL)! Segera konsultasi dokter. ";
                } elseif ($exam->blood_sugar > 126) {
                    $recommendation .= "Gula darah tinggi (>126 mg/dL). Batasi gula dan karbohidrat sederhana. ";
                }
            }

            if ($category === 'lansia') {
                $recommendation .= "Cukupi asupan kalsium dan vitamin D. Lakukan aktivitas fisik ringan setiap hari. ";
            }
        }

        $recommendation .= "Kunjungi Posyandu bulan depan untuk pemantauan lanjutan.";

        return $recommendation;
    }
}
