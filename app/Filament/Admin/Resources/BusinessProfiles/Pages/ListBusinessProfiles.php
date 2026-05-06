<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\Pages;

use App\Filament\Admin\Resources\BusinessProfiles\BusinessProfileResource;
use App\Filament\ListDefaults;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessProfiles extends ListRecords
{
    use ListDefaults;

    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
