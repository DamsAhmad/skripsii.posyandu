<?php

namespace App\Filament\Resources\ExaminationHistoryResource\Pages;

use App\Filament\Resources\ExaminationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateExaminationHistory extends CreateRecord
{
    protected static string $resource = ExaminationHistoryResource::class;

    public static function canCreate(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
