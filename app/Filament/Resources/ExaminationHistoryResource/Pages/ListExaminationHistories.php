<?php

namespace App\Filament\Resources\ExaminationHistoryResource\Pages;

use App\Filament\Resources\ExaminationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminationHistories extends ListRecords
{
    protected static string $resource = ExaminationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
