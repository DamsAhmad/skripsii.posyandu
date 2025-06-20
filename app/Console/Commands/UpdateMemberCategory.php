<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use Illuminate\Console\Scheduling\Schedule;


class UpdateMemberCategory extends Command
{
    protected $signature = 'member:update-category';
    protected $description = 'Update kategori peserta berdasarkan usia dan kondisi kehamilan';

    public function handle()
    {
        $members = Member::all();

        foreach ($members as $member) {
            $newCategory = \App\Filament\Resources\MemberResource::calculateCategory(
                $member->birthdate,
                $member->gender,
                $member->is_pregnant
            );

            if ($member->category !== $newCategory) {
                $member->category = $newCategory;
                $member->save();
                $this->info("Updated member {$member->member_name} to category {$newCategory}");
            }
        }

        $this->info('Kategori peserta berhasil diperbarui.');
        return 0;
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('member:update-category')->dailyAt('00:05');
    }
}
