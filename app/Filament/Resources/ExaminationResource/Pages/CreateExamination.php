<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Services\NutritionalStatusCalculator;
use Illuminate\Support\Facades\Log;

class CreateExamination extends CreateRecord
{
    protected static string $resource = ExaminationResource::class;

    protected static bool $canCreateAnother = false;


    // protected function getCreateFormActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make()->createAnother(false),
    //     ];
    // }

    public function mount(): void
    {
        parent::mount();
        $this->form->fill([
            'checkup_id' => request()->get('checkup_id'),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['checkup_id'])) {
            Log::error('checkup_id missing!', $data);
            throw new \Exception('Missing checkup_id!');
        }

        Log::info('Data received:', $data);
        return $data;
    }

    protected function afterCreate(): void
    {
        Log::info('AFTER CREATE DIPANGGIL');
        $record = $this->record;

        try {
            $member = $record->member;

            $status = NutritionalStatusCalculator::generateStatus($member, $record);
            $z_score = NutritionalStatusCalculator::generateZscore($member, $record);
            $anthropometric_value = NutritionalStatusCalculator::generateAnthropometric($member, $record);
            $recommendation = NutritionalStatusCalculator::generateRecommendation($record);

            $record->update([
                'weight_status' => $status,
                'z_score' => $z_score,
                'anthropometric_value' => $anthropometric_value,
                'recommendation' => $recommendation,
            ]);

            Log::info('RECORD AFTER UPDATE:', $record->fresh()->toArray());

            Notification::make()
                ->title('Status Gizi: ' . $status)
                ->body($recommendation)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::error('GAGAL DI AFTERCREATE: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return CheckupResource::getUrl('edit', ['record' => $this->data['checkup_id']]);
    }

    // protected function getRedirectUrl(): string
    // {
    //     return ExaminationResource::getUrl('index', [
    //         'checkup_id' => $this->record->checkup_id,
    //     ]);
    // }
}
