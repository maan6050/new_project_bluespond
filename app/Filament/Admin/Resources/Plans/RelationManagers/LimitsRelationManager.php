<?php

namespace App\Filament\Admin\Resources\Plans\RelationManagers;

use App\Constants\PlanFeature;
use App\Models\PlanLimit;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class LimitsRelationManager extends RelationManager
{
    protected static string $relationship = 'limits';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('feature_key')
                        ->label(__('Feature'))
                        ->options(collect(PlanFeature::cases())->mapWithKeys(
                            fn (PlanFeature $case) => [$case->value => __($case->label())]
                        )->all())
                        ->required()
                        ->live()
                        ->unique(modifyRuleUsing: function (Unique $rule, Get $get, RelationManager $livewire) {
                            return $rule->where('plan_id', $livewire->ownerRecord->id)->ignore($get('id'));
                        }),

                    Toggle::make('is_enabled')
                        ->label(__('Enabled'))
                        ->helperText(__('Turn off to deny access to this feature on this plan.'))
                        ->default(true),

                    TextInput::make('limit_value')
                        ->label(__('Limit'))
                        ->helperText(__('Numeric limit per billing cycle. Leave blank for unlimited. Ignored for boolean features.'))
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn (Get $get): bool => $get('feature_key')
                            ? PlanFeature::tryFrom($get('feature_key'))?->isQuantitative() === true
                            : false),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('feature_key')
                    ->label(__('Feature'))
                    ->formatStateUsing(fn (string $state): string => __(PlanFeature::tryFrom($state)?->label() ?? $state))
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('Enabled'))
                    ->boolean(),
                TextColumn::make('limit_value')
                    ->label(__('Limit'))
                    ->formatStateUsing(function (?int $state, PlanLimit $record): string {
                        $feature = PlanFeature::tryFrom($record->feature_key);
                        if ($feature && ! $feature->isQuantitative()) {
                            return '—';
                        }

                        return $state === null ? __('Unlimited') : (string) $state;
                    })
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime(config('app.datetime_format'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('feature_key')
            ->modelLabel(__('Plan Limit'));
    }
}
