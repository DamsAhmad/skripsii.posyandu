<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExaminationResource\Pages;
use Filament\Forms\Form;
use Filament\Tables;
use App\Models\Member;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Examination;
use App\Models\Checkup;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Services\NutritionalStatusCalculator;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ExaminationResource extends Resource
{
    protected static ?string $model = Examination::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Pemeriksaan Peserta';
    protected static ?string $modelLabel = 'Pemeriksaan Peserta';
    protected static ?string $pluralModelLabel = 'Pemeriksaan Peserta';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false; // Sembunyikan dari navigasi utama

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('checkup_id')
                    // ->default(request()->get('checkup_id'))
                    // ->state(request()->get('checkup_id'))
                    ->required()
                    ->dehydrated(true),

                Select::make('member_id')
                    ->label('Pilih Peserta')
                    ->options(Member::all()->pluck('member_name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if ($state) {
                            $member = Member::find($state);
                            if ($member) {
                                $set('category', $member->category);
                                $set('is_pregnant', $member->is_pregnant);
                            } else {
                                $set('category', 'dewasa');
                                $set('is_pregnant', false);
                            }
                        } else {
                            $set('category', '');
                            $set('is_pregnant', false);
                        }
                    })
                    ->required()
                    ->createOptionForm([
                        // Form untuk membuat peserta baru
                        Fieldset::make('Data Peserta Baru')->schema([
                            TextInput::make('member_name')
                                ->label('Nama Peserta')
                                ->required(),

                            Select::make('gender')
                                ->label('Jenis Kelamin')
                                ->options([
                                    'Laki-laki' => 'Laki-laki',
                                    'Perempuan' => 'Perempuan',
                                ])
                                ->required(),

                            TextInput::make('birthdate')
                                ->label('Tanggal Lahir')
                                ->type('date')
                                ->required(),

                            TextInput::make('birthplace')
                                ->label('Tempat Lahir')
                                ->required(),
                        ])
                    ])
                    ->createOptionAction(
                        fn($action) => $action->label('Tambah Peserta Baru'),
                    )
                    ->createOptionUsing(function (array $data) {
                        $member = Member::create($data);
                        return $member->id;
                    }),

                TextInput::make('category')
                    ->label('Kategori')
                    ->disabled(),

                Fieldset::make('Data Fisik')
                    ->schema(function ($get) {
                        $category = $get('category') ?? '';
                        $isPregnant = $get('is_pregnant') ?? false;

                        // Pastikan selalu return array minimal dengan field dasar
                        $fields = [
                            TextInput::make('weight')
                                ->label('Berat Badan (kg)')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->step(0.1),

                            TextInput::make('height')
                                ->label('Tinggi Badan (cm)')
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->step(0.1),
                        ];

                        // Tambahkan field berdasarkan kategori
                        switch ($category) {
                            case 'balita':
                                $fields[] = TextInput::make('head_circumference')
                                    ->label('Lingkar Kepala (cm)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1);
                                break;

                            case 'anak-remaja':
                            case 'dewasa':
                            case 'lansia':
                                $fields[] = TextInput::make('abdominal_circumference')
                                    ->label('Lingkar Perut (cm)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1);

                                $fields[] = TextInput::make('tension')
                                    ->label('Tensi (mmHg)')
                                    ->prefix('💓')
                                    ->hint('Format: XXX/XX')
                                    ->rules(['regex:/^\d{2,3}\/\d{2,3}$/'])
                                    ->validationMessages([
                                        'regex' => 'Format harus seperti 120/80',
                                    ]);

                                if (in_array($category, ['dewasa', 'lansia'])) {
                                    $fields[] = TextInput::make('uric_acid')
                                        ->label('Asam Urat (mg/dL)')
                                        ->numeric()
                                        ->step(0.1);

                                    $fields[] = TextInput::make('blood_sugar')
                                        ->label('Gula Darah (mg/dL)')
                                        ->numeric()
                                        ->step(0.1);

                                    $fields[] = TextInput::make('cholesterol')
                                        ->label('Kolesterol (mg/dL)')
                                        ->numeric()
                                        ->step(0.1);
                                }
                                break;
                        }

                        // Tambahkan field khusus ibu hamil
                        if ($isPregnant) {
                            $fields[] = Fieldset::make('Ibu Hamil')
                                ->schema([
                                    TextInput::make('gestational_week')
                                        ->label('Usia Kehamilan (minggu)')
                                        ->required()
                                        ->numeric()
                                        ->minValue(4)
                                        ->maxValue(42)
                                        ->integer(),

                                    TextInput::make('arm_circumference')
                                        ->label('Lingkar Lengan Atas (cm)')
                                        ->required()
                                        ->numeric()
                                        ->minValue(10)
                                        ->maxValue(50)
                                        ->step(0.1)
                                        ->helperText('KEK jika < 23.5 cm'),
                                ])
                                ->columns(2);
                        }

                        return $fields; // Selalu return array
                    })
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member.member_name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('member.gender')
                    ->label('Gender'),

                TextColumn::make('member.age')
                    ->label('Usia')
                    ->state(function ($record) {
                        return $record->member->age;
                    }),

                TextColumn::make('category')
                    ->label('Kategori'),

                TextColumn::make('weight')
                    ->label('BB (kg)'),

                TextColumn::make('height')
                    ->label('TB (cm)'),

                TextColumn::make('head_circumference')
                    ->label('LK')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('abdominal_circumference')
                    ->label('LP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('arm_circumference')
                    ->label('Lila'),

                TextColumn::make('weight_status')
                    ->label('Status Gizi')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, 'Buruk') => 'danger',
                        str_contains($state, 'Kurang') => 'warning',
                        str_contains($state, 'Normal') => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'balita' => 'Balita',
                        'anak-remaja' => 'Anak Remaja',
                        'dewasa' => 'Dewasa',
                        'lansia' => 'Lansia',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Action::make('add_participant')
                    ->label('Tambah Peserta')
                    ->icon('heroicon-o-plus')
                    ->url(fn() => ExaminationResource::getUrl('create', [
                        'checkup_id' => request('checkup_id')
                    ])),

                Action::make('stop_session')
                    ->label('Stop Sesi')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->action(function () {
                        $checkup = Checkup::find(request('checkup_id'));
                        $checkup->update(['status' => 'completed']);

                        return redirect(CheckupResource::getUrl('index'));
                    })
                    ->visible(fn() => Checkup::find(request('checkup_id'))?->status === 'active'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function afterCreate(Examination $record)
    {
        $status = NutritionalStatusCalculator::calculate($record->member, $record);
        $recommendation = NutritionalStatusCalculator::generateRecommendation($record);

        $record->update([
            'weight_status' => $status,
            'recommendation' => $recommendation
        ]);

        Notification::make()
            ->title('Status Gizi: ' . $status)
            ->body($recommendation)
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExaminations::route('/'),
            'create' => Pages\CreateExamination::route('/create'),
            'edit' => Pages\EditExamination::route('/{record}/edit'),
        ];
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();
    //     $checkupId = request('checkup_id');

    //     if ($checkupId) {
    //         $query->where('checkup_id', $checkupId);
    //     } else {
    //         // Fallback jika checkup_id tidak ada
    //         $query->whereNull('checkup_id'); // Atau sesuaikan dengan logika Anda
    //     }

    //     return $query;
    // }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->routeIs('filament.admin.resources.examinations.index')) {
            $checkupId = request('checkup_id');

            if ($checkupId) {
                $query->where('checkup_id', $checkupId);
            } else {
                $query->whereNull('checkup_id');
            }
        }

        return $query;
    }
}
