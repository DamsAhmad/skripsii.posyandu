<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
use App\Models\Checkup;
use App\Models\Examination;
use Filament\Actions;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;


class ListExaminations extends ListRecords
{
    protected static string $resource = ExaminationResource::class;

    protected function getTableHeaderActions(): array
    {
        return [
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

    // public function mount(): void
    // {
    //     $this->redirect(CheckupResource::getUrl());
    // }

    protected function getRedirectUrl(): string
    {
        return ExaminationResource::getUrl('index', [
            'checkup_id' => request()->get('checkup_id'),
        ]);
    }

    public static function getRecord(?string $key): ?Examination
    {
        return static::getModel()::withoutGlobalScopes()->find($key);
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
        $checkupId = request('checkup_id');

        if (!$checkupId) {
            return static::getResource()::getEloquentQuery()->whereRaw('1 = 0');
        }

        return static::getResource()::getEloquentQuery()
            ->where('checkup_id', $checkupId);
    }
}
