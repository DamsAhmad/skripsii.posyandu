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
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            $lila = $exam->arm_circumference;

            return self::calculatePregnantStatus($imt, $lila, $exam->gestational_week);
        }

        $categoryName = $member->category?->name;

        if ($categoryName === 'balita') {
            return self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
        }

        if ($categoryName === 'anak-remaja') {
            return self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
        }

        if (in_array($categoryName, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);

            return [
                'status' => self::categorizeIMT($imt, $categoryName),
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
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            $lila = $exam->arm_circumference;
            return self::combinePregnantStatus(
                self::categorizePregnantIMT($imt),
                ($lila < 23.5 ? 'KEK' : 'Normal'),
                $exam->gestational_week
            );
        }

        $categoryName = $member->category?->name;
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        if ($categoryName === 'balita') {
            $result = self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
            return $result['status'];
        }

        if ($categoryName === 'anak-remaja') {
            $result = self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
            return $result['status'];
        }

        if (in_array($categoryName, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            return self::categorizeIMT($imt, $categoryName);
        }

        return 'Kategori tidak didukung';
    }

    public static function generateZscore(Member $member, Examination $exam): ?float
    {
        $checkupDate = self::getCheckupDate($exam);
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        if (!$exam->weight || !$exam->height) return null;

        $categoryName = $member->category?->name;

        if ($member->is_pregnant || in_array($categoryName, ['dewasa', 'lansia'])) return null;

        if ($categoryName === 'balita') {
            $result = self::calculateWeightForAge($ageInMonths, $exam->weight, $member->gender);
            return $result['z_score'];
        }

        if ($categoryName === 'anak-remaja') {
            $result = self::calculateIMTForAge($ageInMonths, $exam->weight, $exam->height, $member->gender);
            return $result['z_score'];
        }

        return null;
    }

    public static function generateAnthropometric(Member $member, Examination $exam): ?float
    {
        if (!$exam->weight || !$exam->height) return null;

        $categoryName = $member->category?->name;

        if ($member->is_pregnant || in_array($categoryName, ['dewasa', 'lansia'])) {
            $heightInMeters = $exam->height / 100;
            return $exam->weight / ($heightInMeters * $heightInMeters);
        }

        $checkupDate = self::getCheckupDate($exam);
        $ageInMonths = $member->birthdate->diffInMonths($checkupDate);

        if ($categoryName === 'balita') {
            return (float) $exam->weight;
        }

        if ($categoryName === 'anak-remaja') {
            $heightInMeters = $exam->height / 100;
            return $exam->weight / ($heightInMeters * $heightInMeters);
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
            $status = 'Gizi Buruk';
        } elseif ($z_score < -2) {
            $status = 'Gizi Kurang';
        } elseif ($z_score <= 1) {
            $status = 'Normal';
        } elseif ($z_score <= 2) {
            $status = 'Beresiko Gizi Lebih';
        } elseif ($z_score <= 3) {
            $status = 'Gizi Lebih';
        } else {
            $status = 'Obesitas';
        }

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
                'anthropometric_value' => (float) $actualIMT
            ];
        }

        $median = $reference->median;
        $sd_minus = $reference->sd_minus;
        $sd_plus = $reference->sd_plus;

        if ($actualIMT < $median) {
            $z_score = ($actualIMT - $median) / ($median - $sd_minus);
        } else {
            $z_score = ($actualIMT - $median) / ($sd_plus - $median);
        }

        if ($z_score < -3) {
            $status = 'Sangat Kurus';
        } elseif ($z_score >= -3 && $z_score < -2) {
            $status = 'Kurus';
        } elseif ($z_score >= -2 && $z_score <= 1) {
            $status = 'Normal';
        } elseif ($z_score > 1 && $z_score <= 2) {
            $status = 'Risiko Gizi Lebih';
        } elseif ($z_score > 2 && $z_score <= 3) {
            $status = 'Gizi Lebih';
        } else {
            $status = 'Obesitas';
        }

        Log::debug("Reference Data:", [
            'ageMonths' => $ageMonths,
            'gender' => $gender,
            'reference' => $reference,
            'actualIMT' => $actualIMT,
        ]);

        return [
            'status' => $status,
            'z_score' => round($z_score, 1),
            'anthropometric_value' => round((float) $actualIMT, 1)
        ];
    }

    private static function calculatePregnantStatus($imt, $lila, $gestationalWeek = null)
    {
        $imtCategory = self::categorizePregnantIMT($imt);
        $kekStatus = ($lila < 23.5) ? 'KEK' : 'Normal';

        $status = self::combinePregnantStatus($imtCategory, $kekStatus, $gestationalWeek);

        return [
            'status' => $status,
            'z_score' => null,
            'anthropometric_value' => round($imt, 1)
        ];
    }

    private static function combinePregnantStatus($imtCategory, $kekStatus, $gestationalWeek)
    {
        $trimesterSuffix = $gestationalWeek ? ' Trimester ' . ceil($gestationalWeek / 13) : '';

        if ($kekStatus === 'KEK') {
            return $imtCategory . ' + KEK' . $trimesterSuffix;
        }

        return $imtCategory . $trimesterSuffix;
    }

    private static function categorizePregnantIMT($imt)
    {
        if ($imt < 18.5) return 'Kurus';
        if ($imt < 25.0) return 'Normal';
        if ($imt < 30.0) return 'Gemuk (Pra-Obesitas)';
        if ($imt < 35.0) return 'Obesitas Kelas I';
        if ($imt < 40.0) return 'Obesitas Kelas II';
        if ($imt >= 40.0) return 'Obesitas Kelas III';
        return 'Obesitas';
    }

    private static function categorizeIMT($imt, $categoryName)
    {
        if ($categoryName === 'dewasa') {
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
        } elseif ($categoryName === 'lansia') {
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
        $category = $member->category?->name;
        $isPregnant = $member->is_pregnant;

        if (!$category) {
            return "Kategori peserta belum diatur.";
        }

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
