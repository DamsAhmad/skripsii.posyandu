<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Kategori';
    protected static ?string $navigationGroup = 'Manajemen Posyandu';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Kategori Peserta';
    protected static ?string $pluralModelLabel = 'Kategori Peserta';
    protected static bool $shouldRegisterNavigation = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->unique(ignoreRecord: true),

                Grid::make(2)->schema([
                    TextInput::make('min_age_value')
                        ->label('Usia Minimal')
                        ->numeric()
                        ->required()
                        ->dehydrated(),

                    Select::make('min_age_unit')
                        ->label('Satuan')
                        ->options([
                            'bulan' => 'Bulan',
                            'tahun' => 'Tahun',
                        ])
                        ->required()
                        ->dehydrated(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('max_age_value')
                        ->label('Usia Maksimal')
                        ->numeric()
                        ->required()
                        ->dehydrated(),

                    Select::make('max_age_unit')
                        ->label('Satuan')
                        ->options([
                            'bulan' => 'Bulan',
                            'tahun' => 'Tahun',
                        ])
                        ->required()
                        ->dehydrated(),
                ]),

                Toggle::make('for_pregnant')
                    ->label('Kategori untuk Ibu Hamil'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('min_age_months')
                    ->label('Usia Min (bln)')
                    ->sortable(),

                TextColumn::make('max_age_months')
                    ->label('Usia Maks (bln)')
                    ->sortable(),

                IconColumn::make('for_pregnant')
                    ->label('Ibu Hamil?')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Kader')
                        ->modalDescription('Anda yakin ingin menghapus data kader ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(function (Category $record) {
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
    }

    public static function mutateFormDataBeforeFill(array $data): array
    {
        $data['min_age_unit'] = ($data['min_age_months'] % 12 === 0) ? 'tahun' : 'bulan';
        $data['min_age_value'] = ($data['min_age_unit'] === 'tahun')
            ? $data['min_age_months'] / 12
            : $data['min_age_months'];

        $data['max_age_unit'] = ($data['max_age_months'] % 12 === 0) ? 'tahun' : 'bulan';
        $data['max_age_value'] = ($data['max_age_unit'] === 'tahun')
            ? $data['max_age_months'] / 12
            : $data['max_age_months'];

        return $data;
    }


    // public static function mutateFormDataBeforeCreate(array $data): array
    // {
    //     Log::debug('Data sebelum mutate:', $data);

    //     $data['min_age_months'] = ($data['min_age_unit'] === 'tahun')
    //         ? $data['min_age_value'] * 12
    //         : $data['min_age_value'];

    //     $data['max_age_months'] = ($data['max_age_unit'] === 'tahun')
    //         ? $data['max_age_value'] * 12
    //         : $data['max_age_value'];

    //     unset($data['min_age_value'], $data['min_age_unit'], $data['max_age_value'], $data['max_age_unit']);

    //     Log::debug('Data setelah mutate:', $data);

    //     return $data;
    // }


    // public static function mutateFormDataBeforeSave(array $data): array
    // {
    //     return self::mutateFormDataBeforeCreate($data);
    // }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
