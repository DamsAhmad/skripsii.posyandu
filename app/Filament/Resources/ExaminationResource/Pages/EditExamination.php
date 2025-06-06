<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamination extends EditRecord
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // return ExaminationResource::getUrl('index', [
        //     'checkup_id' => $this->record->checkup_id,
        // ]);

        return ExaminationResource::getUrl(name: 'index', parameters: [
            'checkup_id' => request('checkup_id'),
        ]);
    }
}
