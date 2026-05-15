<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\Pages;

use App\Filament\Admin\Resources\BusinessProfiles\BusinessProfileResource;
use App\Filament\CrudDefaults;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditBusinessProfile extends EditRecord
{
    use CrudDefaults;

    protected static string $resource = BusinessProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    #[On('business-profile-updated')]
    public function refreshBusinessProfile(): void
    {
        $this->record->refresh();
    }
}
