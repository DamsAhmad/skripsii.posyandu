<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminations extends ListRecords
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(ExaminationResource::getUrl('create', [
                    'checkup_id' => request('checkup_id')
                ]))
                ->label('Tambah Peserta'),
        ];
    }

    protected function getTableHeading(): string
    {
        $checkupId = request('checkup_id');
        $checkup = \App\Models\Checkup::find($checkupId);

        return $checkup
            ? "Sesi Pemeriksaan: {$checkup->location} ({$checkup->checkup_date})"
            : "Sesi Pemeriksaan";
    }
}
