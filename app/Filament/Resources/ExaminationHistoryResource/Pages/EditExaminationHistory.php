<?php

namespace App\Filament\Resources\ExaminationHistoryResource\Pages;

use App\Filament\Resources\ExaminationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExaminationHistory extends EditRecord
{
    protected static string $resource = ExaminationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
