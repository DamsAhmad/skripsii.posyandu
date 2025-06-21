<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
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
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Get;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $navigationGroup = 'Data Peserta';
    protected static ?string $navigationLabel = 'Data Peserta';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'IfCategoryDataPeserta';
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
                Forms\Components\TextInput::make('member_name')
                    ->label('Nama Peserta')
                    ->placeholder('Masukan nama lengkap peserta')
                    ->required(),
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->placeholder('Masukan 16 digit NIK')
                    ->required()
                    ->numeric()
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if (is_null($value) || $value === '') {
                                $fail('NIK wajib diisi.');
                                return;
                            }

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
                Forms\Components\TextInput::make('no_kk')
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
                Forms\Components\Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->placeholder('Pilih jenis kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\DatePicker::make('birthdate')
                    ->label('Tanggal Lahir')
                    ->required()
                    ->reactive()
                    ->maxDate(now())
                    ->rules(['before_or_equal:' . now()->toDateString()])
                    ->validationMessages([
                        'before_or_equal' => 'Tanggal lahir tidak boleh lebih dari hari ini.',
                    ]),
                Forms\Components\TextInput::make('birthplace')
                    ->label('Tempat Lahir')
                    ->placeholder('Contoh: Jakarta atau Sleman')
                    ->required(),
                Forms\Components\TextInput::make('father')
                    ->label('Nama Ayah')
                    ->placeholder('Masukan Nama Ayah'),
                Forms\Components\TextInput::make('mother')
                    ->label('Nama Ibu')
                    ->placeholder('Masukan Nama Ibu'),
                Forms\Components\TextInput::make('parent_phone')
                    ->label('No. Telepon Orang Tua')
                    ->placeholder('Contoh: 081234567890')
                    ->numeric()
                    ->rule('regex:/^[0-9]{11,13}$/')
                    ->validationMessages([
                        'regex' => 'Nomor telepon harus terdiri dari 11 hingga 13 digit angka.',
                    ]),
                Forms\Components\TextInput::make('nik_parent')
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
                Forms\Components\Select::make('is_pregnant')
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
                            \Carbon\Carbon::parse($get('birthdate'))->diffInMonths(now()) < 180 ||
                            \Carbon\Carbon::parse($get('birthdate'))->diffInMonths(now()) > 600
                    ),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->hidden()
                    ->dehydrated(),
            ]),
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'warning',
                        'ibu hamil' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('age')
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
            ->defaultSort('category_id', 'asc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('lihat_profil')
                        ->label('Profil')
                        ->url(fn($record) => MemberResource::getUrl('view', ['record' => $record]))
                        ->color('info')
                        ->icon('heroicon-o-user')
                        ->iconPosition(IconPosition::After),

                    Tables\Actions\EditAction::make()
                        ->iconPosition(IconPosition::After),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Anggota')
                        ->modalDescription('Anda yakin ingin menghapus data peserta ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(fn(Member $record) => $record->delete())
                        ->iconPosition(IconPosition::After),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-s-chevron-down')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->iconPosition(IconPosition::After)
                    ->button(),
            ]);
    }

    public static function calculateCategory($birthdate, $gender, $isPregnant): ?int
    {
        $ageInMonths = \Carbon\Carbon::parse($birthdate)->diffInMonths(now());

        // Cek kategori khusus untuk ibu hamil
        if ($isPregnant && $gender === 'Perempuan') {
            return \App\Models\Category::where('for_pregnant', true)->value('id');
        }

        // Ambil kategori berdasarkan range umur
        return \App\Models\Category::where('min_age_months', '<=', $ageInMonths)
            ->where('max_age_months', '>=', $ageInMonths)
            ->where(function ($query) {
                $query->whereNull('for_pregnant')->orWhere('for_pregnant', false);
            })
            ->value('id');
    }


    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category_id'] = MemberResource::calculateCategory(
            $data['birthdate'],
            $data['gender'],
            $data['is_pregnant'] ?? false
        );


        Log::info('Category ID:', [$data['category_id']]);


        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['category_id'] = MemberResource::calculateCategory(
            $data['birthdate'],
            $data['gender'],
            $data['is_pregnant'] ?? false
        );

        return $data;
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\MemberResource\Widgets\MemberCharts::class,
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
