<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->convertAgeFields($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->convertAgeFields($data);
    }

    protected function convertAgeFields(array $data): array
    {
        $data['min_age_months'] = ($data['min_age_unit'] === 'tahun')
            ? $data['min_age_value'] * 12
            : $data['min_age_value'];

        $data['max_age_months'] = ($data['max_age_unit'] === 'tahun')
            ? $data['max_age_value'] * 12
            : $data['max_age_value'];

        unset($data['min_age_value'], $data['min_age_unit'], $data['max_age_value'], $data['max_age_unit']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
