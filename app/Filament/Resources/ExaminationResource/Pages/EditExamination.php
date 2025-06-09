<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
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

    protected function afterSave(): void
    {
        $this->redirectUrl(CheckupResource::getUrl('edit', [
            'record' => $this->data['checkup_id']
        ]));
    }
    protected function getRedirectUrl(): string
    {

        return ExaminationResource::getUrl(name: 'index', parameters: [
            'checkup_id' => request('checkup_id'),
        ]);
    }
}
