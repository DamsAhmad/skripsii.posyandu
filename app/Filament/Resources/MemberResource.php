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
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\IconPosition;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;
    protected static ?string $navigationLabel = 'Data Peserta';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $slug = 'DataPeserta';
    protected static ?string $modelLabel = 'Data Peserta';
    protected static ?string $pluralModelLabel = 'Data peserta';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Peserta')->schema([

                    Forms\Components\TextInput::make('member_name')
                        ->label('Nama Peserta')
                        ->placeholder('Masukan nama lengkap peserta')
                        ->required(),
                    Forms\Components\Select::make('gender')
                        ->label('Jenis Kelamin')
                        ->placeholder('Pilih jenis kelamin')
                        ->options([
                            'Laki-laki' => 'Laki-laki',
                            'Perempuan' => 'Perempuan',
                        ])
                        ->required(),
                    Forms\Components\DatePicker::make('birthdate')
                        ->label('Tanggal Lahir')
                        ->required(),
                    Forms\Components\TextInput::make('birthplace')
                        ->label('Tempat Lahir')
                        ->placeholder('Misal: Jakarta atau Sleman')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'balita' => 'info',
                        'anak-remaja' => 'success',
                        'dewasa' => 'primary',
                        'lansia' => 'danger',
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
            ->defaultSort('category', 'asc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'BALITA' => 'balita',
                        'ANAK-REMAJA' => 'anak-Remaja',
                        'DEWASA' => 'dewasa',
                        'LANSIA' => 'lansia',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconPosition(IconPosition::After)
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->modalHeading('Hapus Anggota')
                    ->modalDescription('Anda yakin ingin menghapus data peserta ini? Tindakan ini tidak dapat dibatalkan.')
                    ->action(function (Member $record) {

                        // if ($record->has_examinations) {
                        //     Notification::make()
                        //         ->danger()
                        //         ->title('Gagal Hapus')
                        //         ->body('Anggota ini memiliki riwayat pemeriksaan!')
                        //         ->send();
                        //     return;
                        // }

                        $record->delete();
                    })
                    ->iconPosition(IconPosition::After)
                    ->button()
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

    // public static function calculateCategory($birthdate): string
    // {

    //     $birth = \Carbon\Carbon::parse($birthdate);
    //     $ageInMonths = $birth->diffInMonths(\Carbon\Carbon::now());

    //     if ($ageInMonths <= 60) {
    //         return 'balita';
    //     } elseif ($ageInMonths <= 228) {
    //         return 'anak-remaja';
    //     } elseif ($ageInMonths <= 720) {
    //         return 'dewasa';
    //     } else {
    //         return 'lansia';
    //     }
    // }

    public static function calculateCategory($birthdate): string
    {
        $birth = Carbon::parse($birthdate);
        $ageInMonths = $birth->diffInMonths(Carbon::now());

        if ($ageInMonths <= 60) { // 5 tahun = 60 bulan
            return 'balita';
        } elseif ($ageInMonths <= 228) { // 19 tahun = 228 bulan
            return 'anak-remaja';
        } elseif ($ageInMonths <= 720) { // 60 tahun = 720 bulan
            return 'dewasa';
        } else {
            return 'lansia';
        }
    }
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['category'] = self::calculateCategory($data['birthdate']);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['category'] = self::calculateCategory($data['birthdate']);
        return $data;
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
