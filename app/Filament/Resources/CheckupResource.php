<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckupResource\Pages;
use App\Filament\Resources\CheckupResource\RelationManagers;
use App\Models\Checkup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\ActionSize;
use ExaminationRelationManager;
use Illuminate\Validation\Rule;
use Filament\Forms\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


class CheckupResource extends Resource
{
    protected static ?string $model = Checkup::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Pemeriksaan';
    protected static ?string $navigationLabel = 'Sesi Pemeriksaan';
    protected static ?string $modelLabel = 'Sesi Pemeriksaan';
    protected static ?string $pluralModelLabel = 'Sesi Pemeriksaan';
    protected static ?int $navigationSort = 0;
    protected $attributes = [
        'status' => 'active'
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('checkup_date')
                    ->label('Tanggal Pemeriksaan')
                    ->required()
                    ->default(now())
                    ->rules([
                        fn(Get $get) => Rule::unique('checkups', 'checkup_date')->ignore($get('id')),

                    ])
                    ->validationMessages([
                        'unique' => 'Tanggal pemeriksaan ini sudah ada. Silakan pilih tanggal lain.',
                        'required' => 'Tanggal pemeriksaan wajib diisi.',
                    ]),

                TextInput::make('location')
                    ->label('Lokasi')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Lokasi pemeriksaan wajib diisi',
                    ]),

                Textarea::make('annot')
                    ->label('Catatan')
                    ->columnSpanFull(),

                Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('checkup_date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('examinations_count')
                    ->label('Jumlah Peserta')
                    ->counts('examinations')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Petugas')
                    ->default('Akun petugas telah dihapus')

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ]),
                Tables\Filters\Filter::make('checkup_date')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn($query, $date) =>
                                $query->whereDate('checkup_date', '>=', $date)
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn($query, $date) =>
                                $query->whereDate('checkup_date', '<=', $date)
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_participants')
                        ->label('Masuk Sesi')
                        ->icon('heroicon-o-arrow-right')
                        ->color('info')
                        ->url(fn(Checkup $record) => CheckupResource::getUrl(
                            'edit',
                            ['record' => $record->id]
                        ) . '#relationship-examinations'),
                    Tables\Actions\Action::make('complete')
                        ->label('Selesaikan Sesi')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->action(function (Checkup $record) {
                            $record->update(['status' => 'completed']);
                        })
                        ->visible(fn(Checkup $record) => $record->status === 'active')
                        ->iconPosition(IconPosition::After),

                    Tables\Actions\EditAction::make()
                        ->iconPosition(IconPosition::After),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Sesi Pemeriksaan')
                        ->modalDescription('Anda yakin ingin menghapus sesi Pemeriksaan ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (Checkup $record) {
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
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
    }

    public static function getRelations(): array
    {
        return [
            ExaminationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckups::route('/'),
            'create' => Pages\CreateCheckup::route('/create'),
            'edit' => Pages\EditCheckup::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('examinations');
    }
}
