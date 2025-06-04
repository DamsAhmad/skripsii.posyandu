<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\NutritionalStatusCalculator;

class CreateExamination extends CreateRecord
{
    protected static string $resource = ExaminationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['checkup_id'] = request('checkup_id');
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return ExaminationResource::getUrl('index', [
            'checkup_id' => request('checkup_id')
        ]);
    }

    protected function afterCreate(): void
    {
        // Pindahkan afterCreate logic ke sini
        $record = $this->record;
        $status = NutritionalStatusCalculator::calculate($record->member, $record);
        $recommendation = NutritionalStatusCalculator::generateRecommendation($record);

        $record->update([
            'weight_status' => $status,
            'recommendation' => $recommendation
        ]);

        Notification::make()
            ->title('Status Gizi: ' . $status)
            ->body($recommendation)
            ->success()
            ->send();
    }
}
