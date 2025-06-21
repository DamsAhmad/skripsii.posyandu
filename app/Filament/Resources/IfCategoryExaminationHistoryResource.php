<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExaminationHistoryResource\Pages;
use App\Models\Examination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IfCategoryExaminationHistoryResource extends Resource
{
    // protected static ?string $model = Examination::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Pemeriksaan';
    protected static ?string $navigationGroup = 'Pemeriksaan';
    protected static ?string $modelLabel = 'Riwayat Pemeriksaan Peserta';
    protected static ?string $pluralModelLabel = 'Riwayat Pemeriksaan Peserta';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Tidak perlu form untuk resource ini
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('checkup.checkup_date')
                    ->label('Tanggal Pemeriksaan')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('member.member_name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.category.name')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ?? '-')
                    ->color(fn(string $state): string => match ($state) {
                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'warning',
                        'ibu hamil' => 'danger',
                        default => 'gray'
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('weight')
                    ->label('Berat (kg)')
                    ->numeric(decimalPlaces: 1),

                Tables\Columns\TextColumn::make('height')
                    ->label('Tinggi (cm)')
                    ->numeric(decimalPlaces: 1),

                Tables\Columns\TextColumn::make('arm_circumference')
                    ->label('L. Lengan (cm)')
                    ->numeric(decimalPlaces: 1),

                Tables\Columns\TextColumn::make('head_circumference')
                    ->label('L. Kepala (cm)')
                    ->numeric(decimalPlaces: 1),

                Tables\Columns\TextColumn::make('abdominal_circumference')
                    ->label('L. Perut (cm)')
                    ->numeric(decimalPlaces: 1),
                Tables\Columns\TextColumn::make('tension')
                    ->label('Tensi Darah')
                    ->suffix(' mmHg'),
                Tables\Columns\TextColumn::make('uric_acid')
                    ->label('Asam Urat')
                    ->suffix(' mg/dL'),
                Tables\Columns\TextColumn::make('blood_sugar')
                    ->label('Gula Darah')
                    ->suffix(' mg/dL'),
                Tables\Columns\TextColumn::make('cholesterol')
                    ->label('Kolestrol')
                    ->suffix(' mg/dL'),
                Tables\Columns\TextColumn::make('weight_status')
                    ->label('Status Gizi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Kurus' => 'danger',
                        'Normal' => 'success',
                        'Gemuk' => 'warning',
                        'Obesitas' => 'danger',
                        default => 'gray',
                    }),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('member_id')
                    ->label('Nama Peserta')
                    ->options(fn() => \App\Models\Member::pluck('member_name', 'id'))
                    ->searchable()
                    ->default(request()->get('member_id')) // auto aktif dari query param
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('member_id', $data['value']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (isset($data['value'])) {
                            $name = \App\Models\Member::find($data['value'])?->member_name;
                            return $name ? "Nama: $name" : null;
                        }
                        return null;
                    }),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->options([
                        'balita' => 'Balita',
                        'anak-remaja' => 'Anak Remaja',
                        'dewasa' => 'Dewasa',
                        'lansia' => 'Lansia',
                        'ibu hamil' => 'Ibu Hamil',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('member', function ($q) use ($data) {
                                $q->where('category', $data['value']);
                            });
                        }
                    }),

                Tables\Filters\Filter::make('checkup_date')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'] ?? null, function ($query, $date) {
                                return $query->whereHas('checkup', function ($q) use ($date) {
                                    $q->whereDate('checkup_date', '>=', $date);
                                });
                            })
                            ->when($data['sampai_tanggal'] ?? null, function ($query, $date) {
                                return $query->whereHas('checkup', function ($q) use ($date) {
                                    $q->whereDate('checkup_date', '<=', $date);
                                });
                            });
                    }),
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('member', function ($q) use ($data) {
                                $q->where('gender', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (request()->has('member_id')) {
            $query->where('member_id', request()->get('member_id'));
        }

        return $query;
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
            'index' => Pages\ListExaminationHistories::route('/'),
        ];
    }
}
