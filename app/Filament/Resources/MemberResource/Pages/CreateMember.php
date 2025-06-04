<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected static ?string $title = 'Tambah Peserta Baru';
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = MemberResource::calculateCategory($data['birthdate'] ?? null);
        return $data;
    }
}
