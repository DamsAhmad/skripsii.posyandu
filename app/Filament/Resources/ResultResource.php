<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Aktivitas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('checkup_id')
                ->default(fn() => request()->get('checkup_id'))
                ->dehydrated(true)
                ->required(),

            Forms\Components\Select::make('member_id')
                ->label('Peserta')
                ->relationship('member', 'member_name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('member_name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('gender')
                        ->options([
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan',
                        ])
                        ->required(),

                    Forms\Components\DatePicker::make('birthdate')
                        ->required()
                        ->maxDate(now()),

                    Forms\Components\TextInput::make('birthplace')
                        ->label('Tempat Lahir')
                        ->placeholder('Misal: Jakarta atau Sleman')
                        ->required(),

                    Forms\Components\Toggle::make('is_pregnant')
                        ->label('Sedang Hamil?')
                        ->visible(fn(callable $get) => $get('gender') === 'Perempuan'),
                ])
                ->createOptionUsing(function (array $data) {
                    $ageInYears = \Carbon\Carbon::parse($data['birthdate'])->age;

                    if ($ageInYears <= 5) {
                        $category = 'balita';
                    } elseif ($ageInYears <= 19) {
                        $category = 'anak-remaja';
                    } elseif ($ageInYears <= 59) {
                        $category = 'dewasa';
                    } else {
                        $category = 'lansia';
                    }

                    if ($data['gender'] === 'P' && !empty($data['is_pregnant'])) {
                        $category = 'ibu_hamil';
                    }

                    $member = \App\Models\Member::create([
                        'member_name' => $data['member_name'],
                        'gender' => $data['gender'],
                        'birthdate' => $data['birthdate'],
                        'birthplace' => $data['birthplace'],
                        'is_pregnant' => $data['is_pregnant'] ?? false,
                        'category' => $category,
                    ]);

                    return $member->id;
                })
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) return;
                    $member = \App\Models\Member::find($state);
                    $set('category', $member->category);
                }),

            Forms\Components\TextInput::make('category')
                ->disabled()
                ->label('Kategori'),

            Forms\Components\TextInput::make('weight')
                ->label('Berat Badan (kg)')
                ->numeric()
                ->required()
                ->step(0.1),

            Forms\Components\TextInput::make('height')
                ->label('Tinggi Badan (cm)')
                ->numeric()
                ->visible(fn(callable $get) => in_array($get('category'), ['balita', 'anak-remaja', 'dewasa', 'lansia'])),

            Forms\Components\TextInput::make('hand_circum')
                ->label('Lingkar Lengan Atas (LiLA)')
                ->numeric()
                ->visible(fn(callable $get) => $get('category') === 'ibu_hamil'),

            Forms\Components\TextInput::make('head_circum')
                ->label('Lingkar Kepala (cm)')
                ->numeric()
                ->visible(fn(callable $get) => $get('category') === 'balita'),

            Forms\Components\Textarea::make('notes')
                ->label('Catatan Tambahan')
                ->columnSpanFull(),
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $member = \App\Models\Member::find($data['member_id']);

        $nutrition = \App\Services\GiziAssessmentService::assess(
            $member->category,
            [
                'weight' => $data['weight'] ?? null,
                'height' => $data['height'] ?? null,
                'head_circum' => $data['head_circum'] ?? null,
                'hand_circum' => $data['hand_circum'] ?? null,
                'waist_circum' => $data['waist_circum'] ?? null,
            ],
            $member->birthdate,
            $member->gender
        );

        $data['nutrition_status'] = $nutrition['status'] ?? null;
        $data['z_score'] = $nutrition['z_score'] ?? null;
        $data['bmi'] = $nutrition['bmi'] ?? null;
        $data['category'] = $nutrition['category'] ?? $member->category;

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.member_name')
                    ->label('Nama Peserta')
                    ->searchable(),

                Tables\Columns\TextColumn::make('member.gender')
                    ->label('Jenis Kelamin'),

                Tables\Columns\TextColumn::make('nutrition_status')
                    ->label('Status Gizi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Normal' => 'success',
                        'Kurang Gizi', 'Gizi Buruk' => 'danger',
                        'Lebih' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
