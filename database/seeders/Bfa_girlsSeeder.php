<?php

namespace Database\Seeders;

use App\Models\bfa_girl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class Bfa_girlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = storage_path('app\public\who_standards\bmi_girls.xlsx');


        $rows = Excel::toArray([], $filePath)[0];

        // Lewati header (baris pertama)
        array_shift($rows);

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'Month'  => $row[0] ?? null,
                'L'       => $row[1] ?? null,
                'M'       => $row[2] ?? null,
                'S'       => $row[3] ?? null,
                'SD4neg'  => $row[4] ?? null,
                'SD3neg'  => $row[5] ?? null,
                'SD2neg'  => $row[6] ?? null,
                'SD1neg'  => $row[7] ?? null,
                'SD0'     => $row[8] ?? null,
                'SD1'     => $row[9] ?? null,
                'SD2'     => $row[10] ?? null,
                'SD3'     => $row[11] ?? null,
                'SD4'     => $row[12] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($data, 500) as $chunk) {
            bfa_girl::insert($chunk);
        }
    }
}
