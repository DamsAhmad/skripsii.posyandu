<?php

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables\Table;

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

use App\Models\Examination;
use App\Models\Member;
use App\Models\Checkup;

use Carbon\Carbon;
use Filament\Support\Enums\IconPosition;

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

                        return $fields;
                    })
                    ->columns(2)
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
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
                    })
                    ->before(function ($record) {
                        logger('HAPUS NIH: ' . $record->id);
                    })
                    ->iconPosition(IconPosition::After)
                    ->button()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Peserta')
                    ->icon('heroicon-o-plus')
                    ->url(fn(): string => ExaminationResource::getUrl('create', [
                        'checkup_id' => $this->getOwnerRecord()->id
                    ]))
                // ->openUrlInNewTab(),
                // ->url(fn() => CheckupResource::getUrl('edit', [
                //     'record' => $this->getOwnerRecord()->getKey(),
                // ]) . '#relationship-examinations')
            ]);
    }
}
