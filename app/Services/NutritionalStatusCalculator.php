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

        if (!$exam->weight || !$exam->height) {
            return 'Data tidak lengkap';
        }

        if ($member->is_pregnant) {
            $heightInMeters = $exam->height / 100;
            $imt = $exam->weight / ($heightInMeters * $heightInMeters);
            $lila = $exam->arm_circumference;

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

    public static function generateRecommendation(Examination $exam)
    {
        $member = $exam->member;
        $status = $exam->weight_status;
        $category = $member->category;
        $isPregnant = $member->is_pregnant;

        $recommendation = "Hasil pemeriksaan: $status. ";

        // Rekomendasi berdasarkan kategori dan status gizi
        if ($isPregnant) {
            $lila = $exam->arm_circumference;
            $gestationalWeek = $exam->gestational_week;
            $trimester = ceil($gestationalWeek / 13);

            $recommendation .= "Kehamilan minggu ke-$gestationalWeek (Trimester $trimester). ";

            // Deteksi KEK (Kurang Energi Kronis)
            if (strpos($status, 'KEK') !== false) {
                $recommendation .= "Lingkar Lengan Atas (LiLA) $lila cm (< 23.5 cm) menunjukkan risiko KEK. ";
                $recommendation .= "Perbanyak konsumsi makanan tinggi protein dan energi seperti telur, ikan, daging, kacang-kacangan. ";
                $recommendation .= "Anjurkan suplementasi zat besi dan asam folat. ";
            }

            // Rekomendasi berdasarkan kategori IMT
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

            // Rekomendasi umum untuk semua ibu hamil
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
            // Rekomendasi untuk balita
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

            // Imunisasi
            $ageInMonths = $member->birthdate->diffInMonths(now());
            if ($ageInMonths < 24) {
                $recommendation .= "Pastikan imunisasi lengkap sesuai usia. ";
            }
        } elseif ($category === 'anak-remaja') {
            // Rekomendasi untuk anak remaja
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

            // Edukasi khusus remaja
            $recommendation .= "Edukasi pentingnya sarapan dan pola makan teratur. ";
        } elseif ($category === 'dewasa' || $category === 'lansia') {
            // Rekomendasi untuk dewasa/lansia
            if (strpos($status, 'Kurus') !== false) {
                $recommendation .= "Tingkatkan asupan kalori dengan makanan padat gizi. ";
                $recommendation .= "Konsumsi suplemen jika diperlukan. Pantau kemungkinan penyakit kronis. ";
            } elseif (strpos($status, 'Gemuk') !== false || strpos($status, 'Obesitas') !== false) {
                $recommendation .= "Turunkan berat badan secara bertahap (0.5-1 kg/minggu). ";
                $recommendation .= "Kurangi porsi makan, perbanyak sayur dan buah. Olahraga 30 menit/hari. ";
            } else {
                $recommendation .= "Pertahankan pola makan gizi seimbang. Lakukan aktivitas fisik teratur. ";
            }

            // Rekomendasi berdasarkan pemeriksaan tambahan
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

        // Rekomendasi umum untuk semua kategori
        $recommendation .= "Kunjungi Posyandu bulan depan untuk pemantauan lanjutan.";

        return $recommendation;
    }
}
