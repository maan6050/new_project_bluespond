<?php

namespace App\Filament\Dashboard\Resources\Services;

use App\Filament\Dashboard\Resources\Services\Pages\CreateService;
use App\Filament\Dashboard\Resources\Services\Pages\EditService;
use App\Filament\Dashboard\Resources\Services\Pages\ListServices;
use App\Filament\Dashboard\Resources\Services\RelationManagers\StaffRelationManager;
use App\Filament\Dashboard\Resources\Services\Schemas\ServiceForm;
use App\Filament\Dashboard\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $q) => $q->where('tenant_id', $tenant->id));
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StaffRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Services');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Services');
    }

    public static function getModelLabel(): string
    {
        return __('Service');
    }
}
