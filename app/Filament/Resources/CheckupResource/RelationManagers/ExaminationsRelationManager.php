<?php

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\NutritionalStatusCalculator;
use App\Filament\Resources\ExaminationResource;
use App\Filament\Resources\CheckupResource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Log;
use App\Models\Examination;
use App\Models\Member;
use App\Models\Checkup;
use Carbon\Carbon;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;


class ExaminationRelationManager extends RelationManager
{
    protected static string $relationship = 'examinations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('checkup_id')
                    ->required()
                    ->dehydrated(true),

                Select::make('member_id')
                    ->label('Pilih Peserta')
                    ->options(Member::all()->pluck('member_name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateHydrated(function ($set, $get, $state) {
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
                        \app\Filament\Resources\MemberResource::getFormSchema()
                    ])
                    ->createOptionAction(
                        fn($action) => $action->label('Tambah Peserta Baru'),
                    )
                    ->createOptionUsing(function (array $data) {
                        $birthdate = \Carbon\Carbon::parse($data['birthdate']);
                        $ageInMonths = $birthdate->diffInMonths(now());
                        $ageInYears = $birthdate->age;

                        if ($ageInMonths <= 24) {
                            $data['category'] = 'balita-0-24';
                        } elseif ($ageInMonths <= 59) {
                            $data['category'] = 'balita-25-59';
                        } elseif ($ageInYears <= 18) {
                            $data['category'] = 'anak-remaja';
                        } elseif ($ageInYears <= 59) {
                            $data['category'] = 'dewasa';
                        } else {
                            $data['category'] = 'lansia';
                        }

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
                                        ->label('Usia Kehamilan (minggu)')
                                        ->required()
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(42)
                                        ->integer(),
                                ])
                                ->columns(1);
                        }

                        return $fields;
                    })
                    ->columns(2)
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['member', 'checkup']))

            ->heading('Data Pemeriksaan')
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('member.member_name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member.gender')
                    ->label('Jenis Kelamin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member.category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'danger',
                        'ibu hamil' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('member.birthdate')
                    ->label('Usia Saat Pemeriksaan')
                    ->searchable()
                    ->state(function ($record) {
                        $birthdate = Carbon::parse($record->member->birthdate);
                        $birthcheck = Carbon::parse($record->checkup_date);

                        $diff = $birthdate->diff($birthcheck);

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
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'balita' => 'Balita',
                        'anak-remaja' => 'Anak Remaja',
                        'dewasa' => 'Dewasa',
                        'lansia' => 'Lansia',
                        'ibu hamil' => 'Ibu Hamil',
                    ])
                    ->default(null)
                    ->placeholder('Semua')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('member', function ($q) use ($data) {
                                if ($data['value'] === 'ibu hamil') {
                                    $q->where('category', 'like', '%obesitas%');
                                } else {
                                    $q->where('category', $data['value']);
                                }
                            });
                        }
                        return $query;
                    }),
                Tables\Filters\Filter::make('weight_range')
                    ->label('Berat Badan (kg)')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('min')
                                ->label('BB Minimal')
                                ->numeric()
                                ->dehydrated(),
                            Forms\Components\TextInput::make('max')
                                ->label('BB Maksimal')
                                ->numeric()
                                ->dehydrated(),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(filled($data['min']), fn($q) => $q->where('weight', '>=', $data['min']))
                            ->when(filled($data['max']), fn($q) => $q->where('weight', '<=', $data['max']));
                    }),


                Tables\Filters\Filter::make('tinggi_badan')
                    ->label('Filter Tinggi Badan')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('min')
                                ->label('TB Minimal (cm)')
                                ->numeric()
                                ->dehydrated(),

                            Forms\Components\TextInput::make('max')
                                ->label('TB Maksimal (cm)')
                                ->numeric()
                                ->dehydrated(),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(filled($data['min']), fn($q) => $q->where('height', '>=', $data['min']))
                            ->when(filled($data['max']), fn($q) => $q->where('height', '<=', $data['max']));
                    }),


                Tables\Filters\SelectFilter::make('weight_status')
                    ->label('Status Gizi')
                    ->options([
                        'Normal' => 'Normal',
                        'Kurus' => 'Kurus',
                        'Gemuk' => 'Gemuk',
                        'Obesitas' => 'Obesitas',
                        'Sangat Kurus' => 'Sangat Kurus',
                    ])
                    ->default(null)
                    ->placeholder('Semua')
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('weight_status', 'like', '%' . $data['value'] . '%');
                        }
                        return $query;
                    }),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->modalHeading('Ubah Data Pemeriksaan')
                        ->after(function (Examination $record, array $data) {
                            if (!$record->relationLoaded('checkup')) {
                                $record->load('checkup');
                            }

                            $member = $record->member;

                            if (!$member->category) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Kategori belum diatur')
                                    ->body('Peserta belum memiliki kategori. Silakan atur kategori terlebih dahulu.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $status = \App\Services\NutritionalStatusCalculator::generateStatus($member, $record);
                            $z_score = \App\Services\NutritionalStatusCalculator::generateZscore($member, $record);
                            $anthropometric = \App\Services\NutritionalStatusCalculator::generateAnthropometric($member, $record);
                            $recommendation = \App\Services\NutritionalStatusCalculator::generateRecommendation($record);

                            $record->update([
                                'weight_status' => $status,
                                'z_score' => $z_score,
                                'anthropometric_value' => $anthropometric,
                                'recommendation' => $recommendation,
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Status Gizi Diperbarui: ' . $status)
                                ->body($recommendation)
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Data Pemeriksaan')
                        ->modalDescription('Anda yakin ingin menghapus data pemeriksaan peserta ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (Examination $record) {
                            $record->delete();
                        })
                        ->before(function ($record) {
                            logger('HAPUS NIH: ' . $record->id);
                        })
                        ->iconPosition(IconPosition::After)
                ])
                    ->label('Aksi')
                    ->icon('heroicon-s-chevron-down')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->iconPosition(IconPosition::After)
                    ->button(),

            ])
            ->headerActions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn() => route('export.examinations', ['checkup' => $this->getOwnerRecord()->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('back')
                    ->label('Kembali ke Daftar Sesi')
                    ->icon('heroicon-o-arrow-left')
                    ->color('success')
                    ->url(fn() => route('filament.admin.resources.checkups.index')),

                Tables\Actions\CreateAction::make()
                    ->label('Tambah Peserta')
                    ->icon('heroicon-o-plus')
                    ->url(fn(): string => ExaminationResource::getUrl('create', [
                        'checkup_id' => $this->getOwnerRecord()->id
                    ]))
                    ->visible(fn(): bool => $this->getOwnerRecord()->status === 'active'),

            ]);
    }
}
