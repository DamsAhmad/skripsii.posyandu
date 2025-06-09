<?php

namespace App\Filament\Resources\ExaminationResource\Pages;

use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
use Filament\Actions;
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

        // inject value ke form langsung dari URL
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

        Log::info('RECORD:', $record->toArray());

        try {
            $status = NutritionalStatusCalculator::calculate($record->member, $record);
            $recommendation = NutritionalStatusCalculator::generateRecommendation($record);

            Log::info('STATUS:', [$status]);
            Log::info('REKOMENDASI:', [$recommendation]);

            $record->update([
                'weight_status' => $status,
                'recommendation' => $recommendation,
            ]);
        } catch (\Throwable $e) {
            Log::error('GAGAL DI AFTERCREATE: ' . $e->getMessage());
            throw $e; // biar muncul error putih di browser kalau debug nyala
        }

        Notification::make()
            ->title('Status Gizi: ' . $status)
            ->body($recommendation)
            ->success()
            ->send();

        // $this->redirectUrl(CheckupResource::getUrl('edit', [
        //     'record' => $this->data['checkup_id']
        // ]));
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
