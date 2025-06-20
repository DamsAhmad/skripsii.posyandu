<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExaminationResource\Pages;
use App\Models\Member;
use App\Models\Examination;
use App\Models\Checkup;
use App\Services\NutritionalStatusCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\CheckupResource\RelationManagers\ExaminationRelationManager;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use Filament\Forms\Get;
use Closure;

class ExaminationResource extends Resource
{
    protected static ?string $model = Examination::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Pemeriksaan Peserta';
    protected static ?string $modelLabel = 'Pemeriksaan Peserta';
    protected static ?string $pluralModelLabel = 'Pemeriksaan Peserta';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('checkup_id')
                    ->required()
                    ->dehydrated(true),
                Select::make('member_id')
                    ->label('Cari Peserta')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return Member::query()
                            ->where('member_name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(function ($member) {
                                $age = \Carbon\Carbon::parse($member->birthdate)->diff(now());
                                $ageText = "{$age->y} tahun {$age->m} bulan";
                                return [
                                    $member->id => "{$member->member_name} - {$member->category} - {$ageText}"
                                ];
                            });
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        $member = \App\Models\Member::find($value);
                        if (!$member) return null;

                        $age = \Carbon\Carbon::parse($member->birthdate)->diff(now());
                        $ageText = "{$age->y} tahun {$age->m} bulan";

                        return "{$member->member_name} - {$member->category} - {$ageText}";
                    })

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
                    ->rule(function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            $checkupId = $get('checkup_id');

                            if (!$checkupId || !$value) {
                                return;
                            }

                            $exists = \App\Models\Examination::where('checkup_id', $checkupId)
                                ->where('member_id', $value)
                                ->exists();

                            if ($exists) {
                                $fail("Nama sudah ditemukan di sesi pemeriksaan ini.");
                            }
                        };
                    })
                    ->createOptionForm([
                        Fieldset::make('Data Peserta Baru')->schema([
                            TextInput::make('nik')
                                ->label('NIK')
                                ->placeholder('Masukan NIK')
                                ->required()
                                ->placeholder('Masukan 16 digit NIK')
                                ->maxLength(16)
                                ->rule('digits:16')
                                ->numeric(),
                            TextInput::make('no_kk')
                                ->label('No. KK')
                                ->placeholder('Masukan Nomor KK')
                                ->placeholder('Masukan 16 digit No. KK')
                                ->required()
                                ->maxLength(16)
                                ->rule('digits:16')
                                ->numeric(),
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
                            TextInput::make('father')
                                ->label('Nama Ayah')
                                ->placeholder('Masukan Nama Ayah')
                                ->required(fn($get) => in_array($get('category'), ['balita', 'anak-remaja']))
                                ->dehydrated(fn($get) => in_array($get('category'), ['balita', 'anak-remaja'])),
                            TextInput::make('mother')
                                ->label('Nama Ibu')
                                ->placeholder('Masukan Nama Ibu')
                                ->required(fn($get) => in_array($get('category'), ['balita', 'anak-remaja']))
                                ->dehydrated(fn($get) => in_array($get('category'), ['balita', 'anak-remaja'])),
                            TextInput::make('parent_phone')
                                ->label('No. Telepon Orang Tua')
                                ->placeholder('Contoh: 081234567890')
                                ->numeric()
                                ->maxLength(13)
                                ->rule('regex:/^[0-9]{11,13}$/')
                                ->required(fn($get) => in_array($get('category'), ['balita', 'anak-remaja']))
                                ->dehydrated(fn($get) => in_array($get('category'), ['balita', 'anak-remaja'])),
                            TextInput::make('nik_parent')
                                ->label('NIK Ortu')
                                ->placeholder('Utamakan NIK Ayah/Bapak/Wali laki-laki')
                                ->required(fn($get) => in_array($get('category'), ['balita', 'anak-remaja']))
                                ->dehydrated(fn($get) => in_array($get('category'), ['balita', 'anak-remaja']))
                                ->maxLength(16)
                                ->rule('digits:16')
                                ->numeric(),
                        ])
                    ])

                    ->createOptionAction(
                        fn($action) => $action->label('Tambah Peserta Baru'),
                    )
                    ->createOptionUsing(function (array $data) {
                        $data['is_pregnant'] = filter_var($data['is_pregnant'] ?? false, FILTER_VALIDATE_BOOLEAN);

                        $data['category'] = \App\Filament\Resources\MemberResource::calculateCategory(
                            $data['birthdate'],
                            $data['gender'] ?? null,
                            $data['is_pregnant']
                        );

                        $member = \App\Models\Member::create($data);

                        return $member->id;
                    }),

                TextInput::make('category')
                    ->label('Kategori')
                    ->disabled(),

                Fieldset::make('Data Fisik')
                    ->schema(function ($get) {
                        $category = $get('category') ?? '';
                        $isPregnant = $get('is_pregnant') ?? false;

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
                        switch ($category) {
                            case 'balita':
                                $fields[] = TextInput::make('head_circumference')
                                    ->label('Lingkar Kepala (cm)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1)
                                    ->required();
                                $fields[] = TextInput::make('arm_circumference')
                                    ->label('Lingkar Lengan Atas (cm)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->required()
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(50)
                                    ->step(0.1);
                                break;

                            case 'anak-remaja':
                            case 'dewasa':
                            case 'lansia':
                            case 'ibu hamil':
                                $fields[] = TextInput::make('abdominal_circumference')
                                    ->label('Lingkar Perut (cm)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.1);
                                $fields[] = TextInput::make('arm_circumference')
                                    ->label('Lingkar Lengan Atas (cm)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->required()
                                    ->numeric()
                                    ->minValue(10)
                                    ->maxValue(50)
                                    ->step(0.1);
                                $fields[] = TextInput::make('tension')
                                    ->label('Tensi (mmHg)')
                                    ->prefix('💓')
                                    ->hint('Format: XXX/XX')
                                    ->rules(['regex:/^\d{2,3}\/\d{2,3}$/'])
                                    ->validationMessages([
                                        'regex' => 'Format harus seperti 120/80',
                                    ]);

                                if (in_array($category, ['dewasa', 'lansia', 'ibu hamil'])) {
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

                        if ($isPregnant) {
                            $fields[] = Fieldset::make('Ibu Hamil')
                                ->schema([
                                    TextInput::make('gestational_week')
                                        ->label('Usia Kehamilan (dalam satuan minggu)')
                                        ->required()
                                        ->numeric()
                                        ->minValue(4)
                                        ->maxValue(42)
                                        ->integer(),

                                    // TextInput::make('arm_circumference')
                                    //     ->label('Lingkar Lengan Atas (cm)')
                                    //     ->required()
                                    //     ->numeric()
                                    //     ->minValue(10)
                                    //     ->maxValue(50)
                                    //     ->step(0.1)
                                    //     ->helperText('KEK jika < 23.5 cm'),
                                ])
                                ->columns(1);
                        }

                        return $fields;
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

                Tables\Columns\TextColumn::make('member.gender')
                    ->label('Jenis Kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member.category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('member.birthdate')
                    ->label('Usia')
                    ->state(function ($record) {
                        $birthdate = Carbon::parse($record->member->birthdate);
                        $now = Carbon::now();

                        $diff = $birthdate->diff($now);

                        $years = $diff->y;
                        $months = $diff->m;

                        if ($years === 0 && $months === 0) {
                            return 'Baru lahir';
                        } elseif ($years === 0) {
                            return "{$months} bulan";
                        } elseif ($months === 0) {
                            return "{$years} tahun";
                        } else {
                            return "{$years} tahun {$months} bulan";
                        }
                    }),

                TextColumn::make('weight')
                    ->label('BB (kg)'),

                TextColumn::make('height')
                    ->label('TB (cm)'),

                TextColumn::make('head_circumference')
                    ->label('LK'),

                TextColumn::make('abdominal_circumference')
                    ->label('LP'),

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
            // ->filters([
            //     SelectFilter::make('category')
            //         ->options([
            //             'balita' => 'Balita',
            //             'anak-remaja' => 'Anak Remaja',
            //             'dewasa' => 'Dewasa',
            //             'lansia' => 'Lansia',
            //         ])
            // ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Data Pemeriksaan')
                    ->modalDescription('Anda yakin ingin menghapus data pemeriksaan peserta ini? Tindakan ini tidak dapat dibatalkan.')
                    ->action(function (Examination $record) {
                        $record->delete();
                        return redirect(ExaminationResource::getUrl('index', ['checkup_id' => request()->get('checkup_id')]));
                    })
                    ->iconPosition(IconPosition::After)
                    ->button()
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
            ->duration(0)
            ->persistent()
            ->send();
    }


    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // $checkupId = request('checkup_id') ?? optional(request()->route()?->parameters())['checkup_id'] ?? null;

        // $checkupId = request('checkup_id') ?? request()->route('checkup_id');

        // if ($checkupId) {
        //     $query->where('checkup_id', $checkupId);
        // } else {
        //     $query->whereNull('checkup_id');
        // }

        $checkupId = request('checkup_id');

        if (!$checkupId) {
            $checkupId = request()->route('checkup_id');
        }

        if ($checkupId) {
            $query->where('checkup_id', $checkupId);
        } else {
            $query->whereNull('checkup_id');
        }

        return $query;
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExaminations::route('/'),
            'create' => Pages\CreateExamination::route('/create'),
            'edit' => Pages\EditExamination::route('/{record}/edit'),
        ];
    }
}
