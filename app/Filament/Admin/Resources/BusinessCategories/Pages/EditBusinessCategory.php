<?php

namespace App\Filament\Admin\Resources\BusinessCategories\Pages;

use App\Filament\Admin\Resources\BusinessCategories\BusinessCategoryResource;
use App\Filament\CrudDefaults;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessCategory extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = BusinessCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
