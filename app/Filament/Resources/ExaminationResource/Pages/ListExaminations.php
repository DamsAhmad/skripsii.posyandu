<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
use App\Models\Checkup;
use Filament\Actions;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExaminations extends ListRecords
{
    protected static string $resource = ExaminationResource::class;

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(ExaminationResource::getUrl('create', [
                    'checkup_id' => request('checkup_id'),
                ]))
                ->label('Tambah Peserta'),

            Action::make('stop_session')
                ->label('Stop Sesi')
                ->icon('heroicon-o-stop')
                ->color('danger')
                ->action(function () {
                    $checkup = Checkup::find(request('checkup_id'));
                    if ($checkup) {
                        $checkup->update(['status' => 'completed']);
                    }

                    return redirect(CheckupResource::getUrl('index'));
                })
                ->visible(fn() => Checkup::find(request('checkup_id'))?->status === 'active'),
        ];
    }


    protected function getTableHeading(): string
    {
        $checkupId = request('checkup_id');
        $checkup = Checkup::find($checkupId);

        return $checkup
            ? "Sesi Pemeriksaan: {$checkup->location} ({$checkup->checkup_date})"
            : "Sesi Pemeriksaan";
    }

    protected function getTableQuery(): Builder
    {
        // Gunakan parent query dan filter berdasarkan checkup_id jika ada
        $checkupId = request('checkup_id');

        return static::getResource()::getEloquentQuery()
            ->where('checkup_id', $checkupId);
    }
}
