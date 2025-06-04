<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Wfh_girl;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class Wfh_GirlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = storage_path('app\public\who_standards\wfh_girls.xlsx');


        $rows = Excel::toArray([], $filePath)[0];

        // Lewati header (baris pertama)
        array_shift($rows);

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'Height'  => $row[0] ?? null,
                'L'       => $row[1] ?? null,
                'M'       => $row[2] ?? null,
                'S'       => $row[3] ?? null,
                'SD3neg'  => $row[4] ?? null,
                'SD2neg'  => $row[5] ?? null,
                'SD1neg'  => $row[6] ?? null,
                'SD0'     => $row[7] ?? null,
                'SD1'     => $row[8] ?? null,
                'SD2'     => $row[9] ?? null,
                'SD3'     => $row[10] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($data, 500) as $chunk) {
            Wfh_girl::insert($chunk);
        }
    }
}
