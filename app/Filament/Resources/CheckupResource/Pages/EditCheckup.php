<?php

namespace App\Filament\Resources\CheckupResource\Pages;

use App\Filament\Resources\CheckupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckup extends EditRecord
{
    protected static string $resource = CheckupResource::class;
    public function getTitle(): string
    {
        return 'Lihat Data Sesi Pemeriksaan'; // Atau ganti sesuai mau kamu
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
            // Actions\Action::make('cancel')
            //     ->label('Selesai')
            //     ->url(static::getResource()::getUrl())
            //     ->color('success'),
        ];
    }
}
