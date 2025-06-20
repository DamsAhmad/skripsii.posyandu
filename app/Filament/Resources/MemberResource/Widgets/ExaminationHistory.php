<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use App\Models\Examination;
use App\Models\Member;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ExaminationHistory extends TableWidget
{
    protected static ?string $heading = 'Riwayat Pemeriksaan';
    protected int|string|array $columnSpan = 'full';

    public Member $member;

    public static function makeWithMember(Member $member): static
    {
        $widget = app(static::class);
        $widget->member = $member;

        return $widget;
    }

    protected function getTableQuery(): Builder|Relation
    {
        return $this->member->examinations()->with('checkup')->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('checkup.checkup_date')
                ->label('Tanggal Pemeriksaan')
                ->date('d M Y'),

            Tables\Columns\TextColumn::make('checkup.location')
                ->label('Lokasi'),

            Tables\Columns\TextColumn::make('weight')->label('BB')->suffix(' kg'),
            Tables\Columns\TextColumn::make('height')->label('TB')->suffix(' cm'),
            Tables\Columns\TextColumn::make('arm_circumference')->label('LILA')->suffix(' cm'),
            Tables\Columns\TextColumn::make('abdominal_circumference')->label('Lingkar Perut')->suffix(' cm'),

            Tables\Columns\TextColumn::make('weight_status')
                ->label('Status Gizi')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'Severely Underweight' => 'danger',
                    'Underweight' => 'warning',
                    'Normal' => 'success',
                    'Overweight' => 'warning',
                    'Obese' => 'danger',
                    default => 'gray',
                }),
        ];
    }
}
