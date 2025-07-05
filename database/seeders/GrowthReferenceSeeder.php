<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GrowthReference;
use Maatwebsite\Excel\Facades\Excel;

class GrowthReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama biar gak dobel
        GrowthReference::truncate();

        $files = [
            ['file' => 'bbu_boys.xlsx', 'indicator' => 'bbu', 'gender' => 'Laki-laki'],
            ['file' => 'bbu_girls.xlsx', 'indicator' => 'bbu', 'gender' => 'Perempuan'],
            ['file' => 'imtu_boys.xlsx', 'indicator' => 'imtu', 'gender' => 'Laki-laki'],
            ['file' => 'imtu_girls.xlsx', 'indicator' => 'imtu', 'gender' => 'Perempuan'],
        ];

        foreach ($files as $data) {
            $rows = Excel::toArray([], database_path("nutristandard/" . $data['file']))[0];

            foreach ($rows as $index => $row) {
                if ($index === 0 || !isset($row[0])) continue;

                GrowthReference::create([
                    'indicator'      => $data['indicator'],
                    'gender'         => $data['gender'],
                    'age_months'     => (int) $row[0],
                    'sd_minus_3'     => isset($row[1]) ? (float) $row[1] : null,
                    'sd_minus_2'     => isset($row[2]) ? (float) $row[2] : null,
                    'sd_minus_1'     => isset($row[3]) ? (float) $row[3] : null,
                    'median'         => isset($row[4]) ? (float) $row[4] : null,
                    'sd_plus_1'      => isset($row[5]) ? (float) $row[5] : null,
                    'sd_plus_2'      => isset($row[6]) ? (float) $row[6] : null,
                    'sd_plus_3'      => isset($row[7]) ? (float) $row[7] : null,
                ]);
            }
        }
    }
}
