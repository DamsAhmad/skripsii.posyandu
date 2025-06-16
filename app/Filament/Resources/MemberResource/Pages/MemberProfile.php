<?php

namespace App\Filament\Pages;

use App\Models\Member;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Examination;
use Illuminate\Support\Str;

class MemberProfile extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $title = 'Profil Member';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    public static ?string $slug = 'profil';
    protected static bool $shouldRegisterNavigation = false;

    public ?Member $member = null;
    public ?Examination $latestExamination = null;

    public function mount($record): void
    {
        $this->member = Member::with('examinations')->findOrFail($record);
        $this->latestExamination = $this->member->examinations()->latest()->first();
    }

    public function getHeading(): string
    {
        return 'Profil: ' . $this->member->member_name;
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Pribadi')
                ->schema([
                    Forms\Components\TextInput::make('member_name')
                        ->label('Nama')
                        ->default($this->member->member_name)
                        ->disabled(),

                    Forms\Components\TextInput::make('gender')
                        ->label('Jenis Kelamin')
                        ->default($this->member->gender)
                        ->disabled(),

                    Forms\Components\TextInput::make('birthdate')
                        ->label('Tanggal Lahir')
                        ->default($this->member->birthdate)
                        ->disabled(),

                    Forms\Components\TextInput::make('birthplace')
                        ->label('Tempat Lahir')
                        ->default($this->member->birthplace)
                        ->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Status Gizi Terbaru')
                ->schema([
                    Forms\Components\TextInput::make('weight_status')
                        ->label('Status Berat Badan')
                        ->default($this->latestExamination?->weight_status ?? '-')
                        ->disabled(),
                ]),

            Forms\Components\Section::make('Hasil Pemeriksaan Terbaru')
                ->schema([
                    Forms\Components\TextInput::make('weight')
                        ->label('Berat (kg)')
                        ->default($this->latestExamination?->weight)
                        ->disabled(),

                    Forms\Components\TextInput::make('height')
                        ->label('Tinggi (cm)')
                        ->default($this->latestExamination?->height)
                        ->disabled(),

                    Forms\Components\TextInput::make('arm_circumference')
                        ->label('Lingkar Lengan (cm)')
                        ->default($this->latestExamination?->arm_circumference)
                        ->disabled(),

                    Forms\Components\TextInput::make('head_circumference')
                        ->label('Lingkar Kepala (cm)')
                        ->default($this->latestExamination?->head_circumference)
                        ->disabled(),

                    Forms\Components\TextInput::make('abdominal_circumference')
                        ->label('Lingkar Perut (cm)')
                        ->default($this->latestExamination?->abdominal_circumference)
                        ->disabled(),

                    Forms\Components\TextInput::make('tension')
                        ->label('Tensi')
                        ->default($this->latestExamination?->tension)
                        ->disabled(),

                    Forms\Components\TextInput::make('uric_acid')
                        ->label('Asam Urat')
                        ->default($this->latestExamination?->uric_acid)
                        ->disabled(),

                    Forms\Components\TextInput::make('blood_sugar')
                        ->label('Gula Darah')
                        ->default($this->latestExamination?->blood_sugar)
                        ->disabled(),

                    Forms\Components\TextInput::make('cholesterol')
                        ->label('Kolesterol')
                        ->default($this->latestExamination?->cholesterol)
                        ->disabled(),

                    Forms\Components\Textarea::make('recommendation')
                        ->label('Rekomendasi')
                        ->default($this->latestExamination?->recommendation)
                        ->disabled(),
                ])->columns(2),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->member->examinations()->orderByDesc('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pemeriksaan')
                    ->date('d M Y'),
            ]);
    }

    // Tambah widget grafik di bagian layout (lanjutan di widget terpisah)
}
