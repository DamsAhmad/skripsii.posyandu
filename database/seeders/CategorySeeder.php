<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->delete();

        Category::insert([
            [
                'name' => 'balita',
                'min_age_months' => 0,
                'max_age_months' => 60,
                'for_pregnant' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'anak-remaja',
                'min_age_months' => 61,
                'max_age_months' => 228,
                'for_pregnant' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'dewasa',
                'min_age_months' => 229,
                'max_age_months' => 539,
                'for_pregnant' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'lansia',
                'min_age_months' => 540,
                'max_age_months' => 2000,
                'for_pregnant' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ibu hamil',
                'min_age_months' => 180,
                'max_age_months' => 600,
                'for_pregnant' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
