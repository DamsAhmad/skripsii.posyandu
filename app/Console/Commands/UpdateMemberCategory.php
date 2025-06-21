<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Filament\Resources\MemberResource;

class UpdateMemberCategory extends Command
{
    protected $signature = 'member:update-category';
    protected $description = 'Update category_id peserta berdasarkan usia dan kondisi kehamilan';

    public function handle()
    {
        $updatedCount = 0;

        $members = Member::all();

        foreach ($members as $member) {
            $newCategoryId = MemberResource::calculateCategory(
                $member->birthdate,
                $member->gender,
                $member->is_pregnant
            );

            if ($member->category_id !== $newCategoryId) {
                $member->category_id = $newCategoryId;
                $member->save();

                $this->info("✅ Updated: {$member->member_name} => category_id {$newCategoryId}");
                $updatedCount++;
            }
        }

        $this->info("🎉 Selesai. Total diperbarui: {$updatedCount}");
        return 0;
    }
}
