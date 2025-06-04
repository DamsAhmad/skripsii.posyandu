<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';
    protected static ?string $recordTitleAttribute = 'weight';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('checkup.checkup_date')
                    ->label('Tanggal Checkup')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('BB (kg)')
                    ->numeric(1),

                Tables\Columns\TextColumn::make('height')
                    ->label('TB (cm)')
                    ->numeric(1),

                Tables\Columns\TextColumn::make('z_score')
                    ->label('Z-Score')
                    ->numeric(2)
                    ->color(fn($state) => match (true) {
                        $state < -2 => 'danger',
                        $state < 0 => 'warning',
                        $state > 2 => 'danger',
                        $state > 1 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('nutrition_status')
                    ->label('Status Gizi')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        str_contains($state, 'Buruk') => 'danger',
                        str_contains($state, 'Kurang') => 'warning',
                        str_contains($state, 'Normal') => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('checkup.location')
                    ->label('Lokasi'),
            ])
            ->filters([
                // Bisa tambah filter jika diperlukan
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
