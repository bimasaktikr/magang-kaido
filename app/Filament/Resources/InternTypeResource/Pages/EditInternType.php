<?php

namespace App\Filament\Resources\InternTypeResource\Pages;

use App\Filament\Resources\InternTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternType extends EditRecord
{
    protected static string $resource = InternTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
