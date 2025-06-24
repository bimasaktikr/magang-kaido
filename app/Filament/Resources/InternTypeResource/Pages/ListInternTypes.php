<?php

namespace App\Filament\Resources\InternTypeResource\Pages;

use App\Filament\Resources\InternTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternTypes extends ListRecords
{
    protected static string $resource = InternTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
