<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Resources\MemberResource\Widgets\MemberCharts;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return 'Profil: ' . $this->record->member_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Lihat Grafik')
                ->url(fn() => route('bbu-chart.show', ['id' => $this->record->id]))
                ->icon('heroicon-o-chart-bar')
                ->color('info')
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         MemberCharts::make(['memberId' => $this->record->id]),
    //     ];
    // }

    public function form(Form $form): Form
    {
        $latestExamination = $this->record->examinations()->latest()->first();

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('member_name')
                            ->label('Nama')
                            ->formatStateUsing(fn() => $this->record->member_name)
                            ->disabled(),

                        Forms\Components\TextInput::make('gender')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(fn() => $this->record->gender)
                            ->disabled(),

                        Forms\Components\TextInput::make('birthdate')
                            ->label('Tanggal Lahir')
                            ->formatStateUsing(fn() => Carbon::parse($this->record->birthdate)->translatedFormat('d F Y'))
                            ->disabled(),

                        Forms\Components\TextInput::make('birthplace')
                            ->label('Tempat Lahir')
                            ->formatStateUsing(fn() => $this->record->birthplace ?? '-')
                            ->disabled(),

                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->formatStateUsing(fn() => $this->record->category ?? '-')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Hasil Pemeriksaan Terbaru')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('weight')
                                    ->label('Berat Badan (kg)')
                                    ->formatStateUsing(fn() => $latestExamination?->weight ?? '-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('height')
                                    ->label('Tinggi Badan (cm)')
                                    ->formatStateUsing(fn() => $latestExamination?->height ?? '-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('weight_status')
                                    ->label('Status Gizi')
                                    ->formatStateUsing(fn() => $latestExamination?->weight_status ?? '-')
                                    ->disabled(),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('arm_circumference')
                                    ->label('Lingkar Lengan (cm)')
                                    ->formatStateUsing(fn() => $latestExamination?->arm_circumference ?? '-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('head_circumference')
                                    ->label('Lingkar Kepala (cm)')
                                    ->formatStateUsing(fn() => $latestExamination?->head_circumference ?? '-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('abdominal_circumference')
                                    ->label('Lingkar Perut (cm)')
                                    ->formatStateUsing(fn() => $latestExamination?->abdominal_circumference ?? '-')
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('recommendation')
                            ->label('Rekomendasi')
                            ->formatStateUsing(fn() => $latestExamination?->recommendation ?? '-')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->query($this->record->examinations()->orderByDesc('created_at'))
            ->columns([
                Split::make([
                    TextColumn::make('created_at')
                        ->label('Tanggal')
                        ->date('d M Y')
                        ->weight('bold'),

                    Stack::make([
                        TextColumn::make('weight')
                            ->label('BB')
                            ->suffix(' kg'),
                        TextColumn::make('height')
                            ->label('TB')
                            ->suffix(' cm'),
                    ]),

                    Stack::make([
                        TextColumn::make('arm_circumference')
                            ->label('Lila')
                            ->suffix(' cm'),
                        TextColumn::make('abdominal_circumference')
                            ->label('Lingkar Perut')
                            ->suffix(' cm'),
                    ]),

                    TextColumn::make('weight_status')
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
                ])
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([5, 10, 25]);
    }
}
