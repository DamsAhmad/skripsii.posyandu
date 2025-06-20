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
use ExaminationRelationManager;

class CheckupResource extends Resource
{
    protected static ?string $model = Checkup::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Pemeriksaan';
    protected static ?string $navigationLabel = 'Sesi Pemeriksaan';
    protected static ?string $modelLabel = 'Sesi Pemeriksaan';
    protected static ?string $pluralModelLabel = 'Sesi Pemeriksaan';
    protected static ?int $navigationSort = 1;
    protected $attributes = [
        'status' => 'active'
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('checkup_date')
                    ->label('Tanggal Pemeriksaan')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('location')
                    ->label('Lokasi')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('annot')
                    ->label('Catatan')
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('view_participants')
                    ->label('Lihat Sesi')
                    ->url(fn(Checkup $record) => CheckupResource::getUrl(
                        'edit',
                        ['record' => $record->id]
                    ) . '#relationship-examinations'),
                Tables\Actions\Action::make('complete')
                    ->label('Selesaikan Sesi')
                    ->icon('heroicon-o-check')
                    ->color('danger')
                    ->action(function (Checkup $record) {
                        $record->update(['status' => 'completed']);
                    })
                    ->visible(fn(Checkup $record) => $record->status === 'active'),

                Tables\Actions\EditAction::make()
                    ->iconPosition(IconPosition::After)
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Sesi Pemeriksaan')
                    ->modalDescription('Anda yakin ingin menghapus sesi Pemeriksaan ini? Tindakan ini tidak dapat dibatalkan.')
                    ->action(function (Checkup $record) {
                        $record->delete();
                    })
                    ->iconPosition(IconPosition::After)
                    ->button()
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
