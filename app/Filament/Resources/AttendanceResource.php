<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Internship';

    protected static ?string $navigationLabel = 'Daftar Presensi';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return auth()->check() && $user && method_exists($user, 'hasRole') && $user->hasRole('super_admin');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')->label('student'),
                Tables\Columns\TextColumn::make('date')->label('date'),
                Tables\Columns\TextColumn::make('check_in')->label('checkIn'),
                Tables\Columns\TextColumn::make('check_out')->label('checkOut'),
                Tables\Columns\TextColumn::make('status')
                    ->label('status')
                    ->getStateUsing(function ($record) {
                        $service = app(\App\Services\AttendanceService::class);
                        return $service->getStatusAttendance($record->check_in, $record->check_out);
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
