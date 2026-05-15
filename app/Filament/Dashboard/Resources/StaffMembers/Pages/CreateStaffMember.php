<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Pages;

use App\Filament\Dashboard\Resources\StaffMembers\StaffMemberResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffMember extends CreateRecord
{
    protected static string $resource = StaffMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // tenant_id is never a form field — always derived from the active tenant.
        $data['tenant_id'] = Filament::getTenant()->id;

        return $data;
    }
}
