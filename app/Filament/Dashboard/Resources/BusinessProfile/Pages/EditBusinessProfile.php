<?php

namespace App\Filament\Dashboard\Resources\BusinessProfile\Pages;

use App\Filament\Dashboard\Resources\BusinessProfile\BusinessProfileResource;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditBusinessProfile extends EditRecord
{
    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    #[On('business-profile-updated')]
    public function refreshBusinessProfile(): void
    {
        $this->record->refresh();
    }
}
