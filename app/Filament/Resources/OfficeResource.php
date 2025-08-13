<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Filament\Resources\OfficeResource\RelationManagers;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Office';

    protected static ?string $navigationLabel = 'Office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('name')
                    ->required(),
                Forms\Components\TextInput::make('address')
                    ->label('address')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->label('phone')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('website')
                    ->label('website')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->label('description'),
                Forms\Components\TextInput::make('latitude')
                    ->label('latitude'),
                Forms\Components\TextInput::make('longitude')
                    ->label('longitude'),
                Forms\Components\Select::make('status')
                    ->label('status')
                    ->options([
                        'active' => 'active',
                        'inactive' => 'inactive',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\TimePicker::make('start_time')
                    ->label('start_time')
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->label('end_time')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('address')
                    ->label('address')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('phone')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('website')
                    ->label('website')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('description')
                    ->limit(30)
                    ->wrap(),
                \Filament\Tables\Columns\TextColumn::make('latitude')
                    ->label('latitude'),
                \Filament\Tables\Columns\TextColumn::make('longitude')
                    ->label('longitude'),
                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('status')
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'danger',
                    ])
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('createdAt')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('updatedAt')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('start_time')
                    ->label('start_time')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('end_time')
                    ->label('end_time')
                    ->sortable(),

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
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
