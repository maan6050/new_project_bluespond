<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\Pages;

use App\Filament\Admin\Resources\BusinessProfiles\BusinessProfileResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessProfile extends CreateRecord
{
    use CrudDefaults;

    protected static string $resource = BusinessProfileResource::class;
}
