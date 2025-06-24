<?php

namespace App\Filament\Resources\InternTypeResource\Pages;

use App\Filament\Resources\InternTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInternType extends CreateRecord
{
    protected static string $resource = InternTypeResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
