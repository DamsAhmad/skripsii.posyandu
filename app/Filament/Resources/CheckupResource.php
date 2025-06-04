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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CheckupResource extends Resource
{
    protected static ?string $model = Checkup::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Aktivitas';
    protected static ?string $navigationLabel = 'Sesi Pengecekan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('checkup_date')
                ->label('Tanggal Pengecekan')
                ->required()
                ->default(now())
                ->native(false),

            Forms\Components\TextInput::make('location')
                ->label('Lokasi')
                ->required()
                ->maxLength(100),

            Forms\Components\Textarea::make('annot')
                ->label('Catatan')
                ->columnSpanFull(),

            Forms\Components\Hidden::make('user_id')
                ->default(fn() => Auth::id())
                ->dehydrated(true)
                ->required()
        ]);
    }


    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id(); // fallback kalau user_id belum masuk
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('checkup_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location'),

                Tables\Columns\TextColumn::make('results_count')
                    ->label('Jumlah Peserta')
                    ->counts('results'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('input_data')
                    ->label('Input Data')
                    ->icon('heroicon-m-plus-circle')
                    ->url(fn(Checkup $record) => ResultResource::getUrl('create', ['checkup_id' => $record->id]))
                    ->color('success')
                    ->button(),
                // ->required(),
                Tables\Actions\Action::make('lihat_data')
                    ->label('Lihat Data')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn(Checkup $record) => ResultResource::getUrl('index', ['checkup_id' => $record->id]))
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
            //
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
}
