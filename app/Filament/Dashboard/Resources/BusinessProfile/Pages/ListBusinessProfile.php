<?php

namespace App\Filament\Dashboard\Resources\BusinessProfile\Pages;

use App\Filament\Dashboard\Resources\BusinessProfile\BusinessProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListBusinessProfile extends ListRecords
{
    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
