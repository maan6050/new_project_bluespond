<?php

namespace App\Filament\Dashboard\Resources\BusinessProfile\Pages;

use App\Filament\Dashboard\Resources\BusinessProfile\BusinessProfileResource;
use Filament\Resources\Pages\EditRecord;

class EditBusinessProfile extends EditRecord
{
    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
