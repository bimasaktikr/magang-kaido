<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pendaftaran Magang';

    protected static ?string $navigationGroup = 'Internship';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Nama Mahasiswa')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('intern_type_id')
                    ->label('Tipe Magang')
                    ->relationship('internType', 'name')
                    ->required(),
                Forms\Components\DatePicker::make('req_start_date')
                    ->label('Tanggal Mulai (Pengajuan)')
                    ->required(),
                Forms\Components\DatePicker::make('req_end_date')
                    ->label('Tanggal Selesai (Pengajuan)')
                    ->required(),
                Forms\Components\DatePicker::make('accepted_start_date')
                    ->label('Tanggal Mulai Magang'),
                Forms\Components\DatePicker::make('accepted_end_date')
                    ->label('Tanggal Selesai Magang'),
                // ApplicationResource::form()
                Forms\Components\Select::make('status')
                    ->label('status')
                    ->options([
                        'diproses' => 'diproses',
                        'hold'     => 'hold',      // <— NEW (opsional tapi disarankan)
                        'diterima' => 'diterima',
                        'ditolak'  => 'ditolak',
                    ])
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Nama Mahasiswa'),
                TextColumn::make('internType.name')
                    ->label('Tipe Magang'),
                TextColumn::make('req_start_date')
                    ->label('Tanggal Mulai Pengajuan')
                    ->date(),
                TextColumn::make('req_end_date')
                    ->label('Tanggal Selesai Pengajuan')
                    ->date(),
                TextColumn::make('accepted_start_date')
                    ->label('Tanggal Mulai Magang')
                    ->date(),
                TextColumn::make('accepted_end_date')
                    ->label('Tanggal Selesai Magang')
                    ->date(),
                TextColumn::make('status')
                    ->label('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diproses' => 'warning',
                        'hold'     => 'info',     // <— NEW
                        'diterima' => 'success',
                        'ditolak'  => 'danger',
                        default    => 'gray',
                    }),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), // <— NEW
                Tables\Actions\DeleteAction::make(),

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
            'view'   => Pages\ViewApplication::route('/{record}'),     // <— NEW
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
