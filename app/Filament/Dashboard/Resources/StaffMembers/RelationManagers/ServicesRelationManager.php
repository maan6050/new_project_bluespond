<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Manages which services a given staff member can perform.
 *
 * Shown on the Staff Member edit page. Mirrors StaffRelationManager from the
 * Service side — both edit the same service_staff pivot.
 */
class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    protected static ?string $title = 'Services';

    public function form(Schema $schema): Schema
    {
        // Used by the row EditAction to edit the pivot overrides.
        return $schema->components(self::pivotFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(40)
                    ->extraImgAttributes(['class' => 'rounded-lg']),

                TextColumn::make('name')
                    ->label(__('Service'))
                    ->searchable()
                    ->description(fn ($record) => $record->category),

                TextColumn::make('price')
                    ->label(__('Service Price'))
                    ->money('USD', divideBy: 100),

                TextColumn::make('custom_price')
                    ->label(__('Custom Price'))
                    ->state(fn ($record): ?string => $record->pivot->custom_price !== null
                        ? '$'.number_format($record->pivot->custom_price / 100, 2)
                        : null)
                    ->placeholder(__('Service default')),

                TextColumn::make('custom_duration')
                    ->label(__('Custom Duration'))
                    ->state(fn ($record): ?string => $record->pivot->custom_duration !== null
                        ? $record->pivot->custom_duration.' '.__('min')
                        : null)
                    ->placeholder(__('Service default')),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('Assign Service'))
                    ->modalHeading(__('Assign a service'))
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(
                        fn (Builder $query): Builder => $query->where('tenant_id', Filament::getTenant()->id)
                    )
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        ...self::pivotFields(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Edit overrides'))
                    ->modalHeading(__('Edit per-staff overrides')),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Pivot fields shared by the Attach modal and the row Edit modal.
     *
     * @return array<int, TextInput>
     */
    private static function pivotFields(): array
    {
        return [
            TextInput::make('custom_price')
                ->label(__('Custom Price'))
                ->helperText(__('Optional. Overrides the service price for this staff member.'))
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->prefix('$')
                ->formatStateUsing(fn (?int $state): ?float => $state !== null ? $state / 100 : null)
                ->dehydrateStateUsing(fn ($state): ?int => ($state === null || $state === '')
                    ? null
                    : (int) round(((float) $state) * 100)),

            TextInput::make('custom_duration')
                ->label(__('Custom Duration'))
                ->helperText(__('Optional. Overrides the service duration for this staff member.'))
                ->numeric()
                ->minValue(5)
                ->maxValue(1440)
                ->step(5)
                ->suffix(__('minutes')),
        ];
    }
}
