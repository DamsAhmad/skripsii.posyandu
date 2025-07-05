<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Posyandu';
    protected static ?string $navigationLabel = 'Data Kader';
    protected static ?int $navigationSort = 0;
    protected static ?string $modelLabel = 'Data Kader';
    protected static ?string $pluralModelLabel = 'Data Kader';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('E-mail')
                ->required()
                ->email()
                ->maxLength(255)
                // ->unique(ignoreRecord: true),
                ->rules([
                    fn(Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $userId = $get('id');
                        $exists = \App\Models\User::where('email', $value)
                            ->when($userId, fn($q) => $q->where('id', '!=', $userId))
                            ->exists();

                        if ($exists) {
                            $fail('Email sudah terdaftar.');
                        }
                    }
                ]),
            Select::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->options([
                    'Laki-laki' => 'Laki-laki',
                    'Perempuan' => 'Perempuan',
                ])
                ->required(),
            TextInput::make('password')
                ->password()
                ->label('Password')
                ->maxLength(255)
                ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                ->required(fn(string $context) => $context === 'create')
                ->dehydrated(fn($state) => filled($state)),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('No.')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'Laki-laki' => 'info',
                        'Perempuan' => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Kader')
                        ->modalDescription('Anda yakin ingin menghapus data kader ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (User $record) {
                            $record->delete();
                        })
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
