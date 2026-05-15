<?php

namespace App\Filament\Admin\Resources\BusinessCategories\Pages;

use App\Filament\Admin\Resources\BusinessCategories\BusinessCategoryResource;
use App\Filament\CrudDefaults;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessCategory extends CreateRecord
{
    use CrudDefaults;

    protected static string $resource = BusinessCategoryResource::class;
}
