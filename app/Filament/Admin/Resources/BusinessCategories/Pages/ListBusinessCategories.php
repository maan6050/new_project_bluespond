<?php

namespace App\Filament\Admin\Resources\BusinessCategories\Pages;

use App\Filament\Admin\Resources\BusinessCategories\BusinessCategoryResource;
use App\Filament\ListDefaults;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessCategories extends ListRecords
{
    use ListDefaults;

    protected static string $resource = BusinessCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
