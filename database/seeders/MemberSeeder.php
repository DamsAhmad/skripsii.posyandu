<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        Member::query()->delete();

        $faker = Faker::create('id_ID');

        $categories = [
            'balita' => [0, 60],
            'anak-remaja' => [61, 228],
            'dewasa' => [229, 720],
            'lansia' => [721, 1200],
            'ibu hamil' => [180, 600],
        ];

        foreach ($categories as $category => [$minMonths, $maxMonths]) {
            $count = 0;

            while ($count < 3) {
                $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);
                $birthdate = $this->generateBirthdate($minMonths, $maxMonths);
                $ageInMonths = Carbon::parse($birthdate)->diffInMonths(Carbon::now());

                $isPregnant = $category === 'ibu hamil' && $gender === 'Perempuan';

                if ($category === 'ibu hamil' && !$isPregnant) {
                    continue; // skip cowok
                }

                Member::create([
                    'nik' => $faker->unique()->numerify(str_repeat('#', 16)),
                    'no_kk' => $faker->numerify(str_repeat('#', 16)),
                    'member_name' => $faker->name(),
                    'gender' => $gender,
                    'birthdate' => $birthdate,
                    'birthplace' => $faker->city,
                    'category' => $category,
                    'father' => in_array($category, ['balita', 'anak-remaja']) ? $faker->name('male') : null,
                    'mother' => in_array($category, ['balita', 'anak-remaja']) ? $faker->name('female') : null,
                    'nik_parent' => in_array($category, ['balita', 'anak-remaja']) ? $faker->numerify(str_repeat('#', 16)) : null,
                    'parent_phone' => in_array($category, ['balita', 'anak-remaja'])
                        ? $faker->numerify('08' . str_repeat('#', rand(9, 11)))
                        : null,
                    'is_pregnant' => $isPregnant,
                ]);

                $count++;
            }
        }
    }

    private function generateBirthdate(int $minMonths, int $maxMonths): string
    {
        $months = rand($minMonths, $maxMonths);
        return now()->subMonths($months)->format('Y-m-d');
    }
}
