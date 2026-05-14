<?php

namespace App\Filament\Dashboard\Resources\Services\Tables;

use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(40)
                    ->extraImgAttributes(['class' => 'rounded-lg']),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->category),

                TextColumn::make('duration_minutes')
                    ->label(__('Duration'))
                    ->formatStateUsing(fn (int $state): string => $state.' '.__('min'))
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('USD', divideBy: 100)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->placeholder(__('All')),
                TernaryFilter::make('is_public')
                    ->label(__('Public'))
                    ->placeholder(__('All')),
                TrashedFilter::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope(SoftDeletingScope::class))
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(fn (Service $record): string => self::isLastActiveService($record)
                        ? __('Delete your only active service?')
                        : __('Delete service'))
                    ->modalDescription(fn (Service $record): ?string => self::lastServiceDeleteWarning($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * True when this is the only currently-active service for the tenant —
     * regardless of publish state. Deleting it means the business will have
     * no bookable offering, which is worth warning the owner about even if
     * they're already unpublished.
     */
    private static function isLastActiveService(Service $record): bool
    {
        if (! $record->is_active) {
            return false;
        }

        return Service::where('tenant_id', $record->tenant_id)
            ->where('is_active', true)
            ->where('id', '!=', $record->id)
            ->count() === 0;
    }

    private static function lastServiceDeleteWarning(Service $record): ?string
    {
        if (! self::isLastActiveService($record)) {
            return null;
        }

        $profile = $record->tenant?->businessProfile;

        if ($profile?->is_published) {
            return __('This is your only active service. Deleting it will unpublish your business until you add another, so customers will not be able to book.');
        }

        return __('This is your only active service. Without it your business cannot go live until you add another.');
    }
}
