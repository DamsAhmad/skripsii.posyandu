<?php

namespace App\Filament\Resources\CheckupResource\Pages;

use App\Filament\Resources\CheckupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckups extends ListRecords
{
    protected static string $resource = CheckupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
