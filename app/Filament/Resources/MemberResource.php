<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\Widgets\MemberCharts;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Carbon\Carbon;
use Closure;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Illuminate\Validation\Rule;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $navigationGroup = 'Data Peserta';
    protected static ?string $navigationLabel = 'Data Peserta Total';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'DataPeserta';
    protected static ?string $modelLabel = 'Data Peserta';
    protected static ?string $pluralModelLabel = 'Data peserta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        return [
            Section::make('Data Peserta')->schema([
                TextInput::make('member_name')
                    ->label('Nama Peserta')
                    ->placeholder('Masukan nama lengkap peserta')
                    ->required(),
                TextInput::make('nik')
                    ->label('NIK')
                    ->placeholder('Masukan 16 digit NIK')
                    ->required()
                    ->numeric()
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                            // if (is_null($value) || $value === '') {
                            //     $fail('NIK wajib diisi.');
                            //     return;
                            // }

                            if (!is_numeric($value)) {
                                $fail('NIK harus berupa angka.');
                                return;
                            }

                            $length = strlen($value);
                            if ($length < 16) {
                                $fail('Angka yang Anda masukkan kurang dari 16 digit.');
                            } elseif ($length > 16) {
                                $fail('Angka yang Anda masukkan lebih dari 16 digit.');
                            }

                            $memberId = $get('id');
                            $exists = \App\Models\Member::where('nik', $value)
                                ->when($memberId, fn($q) => $q->where('id', '!=', $memberId))
                                ->exists();

                            if ($exists) {
                                $fail('NIK sudah terdaftar.');
                            }
                        }
                    ]),
                TextInput::make('no_kk')
                    ->label('No. KK')
                    ->placeholder('Masukan 16 digit No. KK')
                    ->required()
                    ->numeric()
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if (is_null($value) || $value === '') {
                                $fail('No. KK wajib diisi.');
                                return;
                            }

                            if (!is_numeric($value)) {
                                $fail('No. KK harus berupa angka.');
                                return;
                            }

                            $length = strlen($value);
                            if ($length < 16) {
                                $fail('Angka yang Anda masukkan kurang dari 16 digit.');
                            } elseif ($length > 16) {
                                $fail('Angka yang Anda masukkan lebih dari 16 digit.');
                            }
                        }
                    ]),
                Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->placeholder('Pilih jenis kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->required()
                    ->reactive(),
                DatePicker::make('birthdate')
                    ->label('Tanggal Lahir')
                    ->required()
                    ->reactive()
                    ->maxDate(now())
                    ->rules(['before_or_equal:' . now()->toDateString()])
                    ->validationMessages([
                        'before_or_equal' => 'Tanggal lahir tidak boleh lebih dari hari ini.',
                    ]),
                TextInput::make('birthplace')
                    ->label('Tempat Lahir')
                    ->placeholder('Contoh: Jakarta atau Sleman')
                    ->required(),
                TextInput::make('father')
                    ->label('Nama Ayah')
                    ->placeholder('Masukan Nama Ayah'),
                TextInput::make('mother')
                    ->label('Nama Ibu')
                    ->placeholder('Masukan Nama Ibu'),
                TextInput::make('parent_phone')
                    ->label('No. Telepon Orang Tua')
                    ->placeholder('Contoh: 081234567890')
                    ->numeric()
                    ->rule('regex:/^[0-9]{11,13}$/')
                    ->validationMessages([
                        'regex' => 'Nomor telepon harus terdiri dari 11 hingga 13 digit angka.',
                    ]),
                TextInput::make('nik_parent')
                    ->label('NIK Ortu')
                    ->placeholder('Masukan NIK Orang Tua')
                    ->numeric()
                    ->default(fn($record) => $record?->nik_parent)
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if (!is_numeric($value)) {
                                $fail('NIK Ortu harus berupa angka.');
                                return;
                            }

                            $length = strlen($value);
                            if ($length < 16) {
                                $fail('Angka yang Anda masukkan kurang dari 16 digit.');
                            } elseif ($length > 16) {
                                $fail('Angka yang Anda masukkan lebih dari 16 digit.');
                            }
                        }
                    ]),
                Select::make('is_pregnant')
                    ->label('Sedang hamil?')
                    ->options([
                        false => 'Tidak',
                        true => 'Ya',
                    ])
                    ->native(false)
                    ->required()
                    ->default(false)
                    ->hidden(
                        fn($get) =>
                        $get('gender') !== 'Perempuan' ||
                            Carbon::parse($get('birthdate'))->diffInMonths(now()) < 180 ||
                            Carbon::parse($get('birthdate'))->diffInMonths(now()) > 600
                    ),
            ]),
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'danger',
                        'ibu hamil' => 'danger',
                    }),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->searchable(),
                TextColumn::make('age')
                    ->label('Usia')
                    ->state(function ($record) {
                        $birthdate = Carbon::parse($record->birthdate);
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
            ])
            ->defaultSort('category', 'asc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'balita' => 'balita',
                        'anak-remaja' => 'anak remaja',
                        'dewasa' => 'dewasa',
                        'lansia' => 'lansia',
                        'ibu hamil' => 'ibu hamil',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('category', $data['value']);
                        }
                    }),
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('gender', $data['value']);
                        }
                    }),
                Filter::make('usia')
                    ->label('Filter Usia')
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('min_age_value')
                                ->label('Usia Minimal')
                                ->numeric()
                                ->dehydrated(),

                            Select::make('min_age_unit')
                                ->label('Satuan')
                                ->options([
                                    'bulan' => 'Bulan',
                                    'tahun' => 'Tahun',
                                ])
                                ->dehydrated(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('max_age_value')
                                ->label('Usia Maksimal')
                                ->numeric()
                                ->dehydrated(),

                            Select::make('max_age_unit')
                                ->label('Satuan')
                                ->options([
                                    'bulan' => 'Bulan',
                                    'tahun' => 'Tahun',
                                ])
                                ->dehydrated(),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $today = Carbon::today();

                        if (!empty($data['min_age_value']) && !empty($data['min_age_unit'])) {
                            $unit = $data['min_age_unit'];
                            $value = (int) $data['min_age_value'];

                            $maxBirthdate = $unit === 'bulan'
                                ? $today->copy()->subMonths($value)
                                : $today->copy()->subYears($value);

                            $query->whereDate('birthdate', '<=', $maxBirthdate);
                        }

                        if (!empty($data['max_age_value']) && !empty($data['max_age_unit'])) {
                            $unit = $data['max_age_unit'];
                            $value = (int) $data['max_age_value'];

                            $minBirthdate = $unit === 'bulan'
                                ? $today->copy()->subMonths($value + 1)->addDay()
                                : $today->copy()->subYears($value + 1)->addDay();

                            $query->whereDate('birthdate', '>=', $minBirthdate);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('lihat_profil')
                        ->label('Profil')
                        ->url(fn($record) => MemberResource::getUrl('view', ['record' => $record]))
                        ->color('info')
                        ->icon('heroicon-o-user')
                        ->iconPosition(IconPosition::After),
                    EditAction::make()
                        ->iconPosition(IconPosition::After),
                    DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Anggota')
                        ->modalDescription('Anda yakin ingin menghapus data peserta ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (Member $record) {
                            $record->delete();
                        })
                        ->iconPosition(IconPosition::After)
                ])
                    ->label('Aksi')
                    ->icon('heroicon-s-chevron-down')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->iconPosition(IconPosition::After)
                    ->button(),

            ]);
    }

    public static function calculateCategory($birthdate,  $gender = null, $isPregnant = false): string
    {
        $birth = Carbon::parse($birthdate);
        $ageInMonths = $birth->diffInMonths(Carbon::now());

        if ($isPregnant && $gender === 'Perempuan' && $ageInMonths >= 180 && $ageInMonths <= 600) {
            return 'ibu hamil';
        }
        if ($ageInMonths <= 60) {
            return 'balita';
        } elseif ($ageInMonths <= 228) {
            return 'anak-remaja';
        } elseif ($ageInMonths <= 539) {
            return 'dewasa';
        } else {
            return 'lansia';
        }
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_pregnant'] = filter_var($data['is_pregnant'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['category'] = self::calculateCategory($data['birthdate'], $data['gender'] ?? null, $data['is_pregnant']);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_pregnant'] = filter_var($data['is_pregnant'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['category'] = self::calculateCategory($data['birthdate'], $data['gender'] ?? null, $data['is_pregnant']);
        return $data;
    }

    public static function getWidgets(): array
    {
        return [
            MemberCharts::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
            'view' => Pages\ViewMember::route('/{record}'),
        ];
    }
}
