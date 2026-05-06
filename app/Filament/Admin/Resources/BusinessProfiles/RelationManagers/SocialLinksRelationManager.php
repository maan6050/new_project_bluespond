<?php

namespace App\Filament\Admin\Resources\BusinessProfiles\RelationManagers;

use App\Constants\SocialPlatform;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SocialLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'socialLinks';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('platform')
                        ->label(__('Platform'))
                        ->options(collect(SocialPlatform::cases())->mapWithKeys(
                            fn (SocialPlatform $case) => [$case->value => __(Str::headline($case->value))]
                        )->all())
                        ->required(),
                    TextInput::make('url')
                        ->label(__('URL'))
                        ->url()
                        ->required()
                        ->maxLength(500),
                    TextInput::make('sort_order')
                        ->label(__('Sort Order'))
                        ->numeric()
                        ->default(0),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform')
                    ->label(__('Platform'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Str::headline($state)))
                    ->sortable(),
                TextColumn::make('url')
                    ->label(__('URL'))
                    ->limit(50)
                    ->url(fn ($record): string => $record->url)
                    ->openUrlInNewTab(),
                TextColumn::make('sort_order')
                    ->label(__('Order'))
                    ->sortable(),
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
            ->defaultSort('sort_order')
            ->modelLabel(__('Social Link'));
    }
}
