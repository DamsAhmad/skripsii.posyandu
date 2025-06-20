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
            Action::make('Kembali Ke List Member')
                ->label('Kembali ke List Peserta')
                ->icon('heroicon-o-arrow-left')
                ->color('success')
                ->url(fn() => url('/admin/DataPeserta')),
            Action::make('Lihat Grafik')
                ->url(function () {
                    $category = $this->record->category;
                    $gender = $this->record->gender;

                    return match (true) {
                        $category === 'balita' && $gender === 'Laki-laki'     => route('bbuboy-chart.show', ['id' => $this->record->id]),
                        $category === 'balita' && $gender === 'Perempuan'     => route('bbugirl-chart.show', ['id' => $this->record->id]),
                        $category === 'anak-remaja' && $gender === 'Laki-laki' => route('imtuboy-chart.show', ['id' => $this->record->id]),
                        $category === 'anak-remaja' && $gender === 'Perempuan' => route('imtugirl-chart.show', ['id' => $this->record->id]),
                        in_array($category, ['dewasa', 'lansia', 'ibu hamil'])             => route('imtadult-chart.show', ['id' => $this->record->id]),
                        default => null,
                    };
                })
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->hidden(function () {
                    $category = $this->record->category;
                    $gender = $this->record->gender;

                    return ! (
                        ($category === 'balita' && in_array($gender, ['Laki-laki', 'Perempuan'])) ||
                        ($category === 'anak-remaja' && in_array($gender, ['Laki-laki', 'Perempuan'])) ||
                        in_array($category, ['dewasa', 'lansia']) ||
                        ($category === 'ibu hamil' && $gender === 'Perempuan')
                    );
                }),
        ];
    }


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
