<?php

namespace App\Filament\Dashboard\Resources\Services\Pages;

use App\Filament\Dashboard\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenantId = Filament::getTenant()->id;

        $data['tenant_id'] = $tenantId;
        $data['slug'] = Service::generateUniqueSlug($tenantId, $data['name']);

        return $data;
    }
}
