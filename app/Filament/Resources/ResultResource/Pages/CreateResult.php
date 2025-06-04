<?php

namespace App\Filament\Resources\ResultResource\Pages;

use App\Filament\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateResult extends CreateRecord
{
    protected static string $resource = ResultResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        $member = \App\Models\Member::find($this->data['member_id']);

        $nutrition = \App\Services\GiziAssessmentService::assess(
            $member->category,
            $this->data,
            $member->birthdate,
            $member->gender
        );

        $this->data['nutrition_status'] = $nutrition['status'];
    }
}
