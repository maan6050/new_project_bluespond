<?php

namespace App\Filament\Dashboard\Resources\Services\Pages;

use App\Filament\Dashboard\Resources\Services\ServiceResource;
use App\Models\Service;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(fn (Service $record): string => $this->isLastActive($record)
                    ? __('Delete your only active service?')
                    : __('Delete service'))
                ->modalDescription(fn (Service $record): ?string => $this->deleteWarning($record)),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function isLastActive(Service $record): bool
    {
        if (! $record->is_active) {
            return false;
        }

        return Service::where('tenant_id', $record->tenant_id)
            ->where('is_active', true)
            ->where('id', '!=', $record->id)
            ->count() === 0;
    }

    private function deleteWarning(Service $record): ?string
    {
        if (! $this->isLastActive($record)) {
            return null;
        }

        $profile = $record->tenant?->businessProfile;

        if ($profile?->is_published) {
            return __('This is your only active service. Deleting it will unpublish your business until you add another, so customers will not be able to book.');
        }

        return __('This is your only active service. Without it your business cannot go live until you add another.');
    }
}
