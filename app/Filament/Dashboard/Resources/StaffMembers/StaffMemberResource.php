<?php

namespace App\Filament\Dashboard\Resources\StaffMembers;

use App\Filament\Dashboard\Resources\StaffMembers\Pages\CreateStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\ListStaffMembers;
use App\Filament\Dashboard\Resources\StaffMembers\Schemas\StaffMemberForm;
use App\Filament\Dashboard\Resources\StaffMembers\Tables\StaffMembersTable;
use App\Models\StaffMember;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffMemberResource extends Resource
{
    protected static ?string $model = StaffMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    // Sits between Services (10) and Business Settings (20) in the sidebar.
    protected static ?int $navigationSort = 15;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $q) => $q->where('tenant_id', $tenant->id));
    }

    public static function form(Schema $schema): Schema
    {
        return StaffMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffMembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffMembers::route('/'),
            'create' => CreateStaffMember::route('/create'),
            'edit' => EditStaffMember::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Staff');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Staff');
    }

    public static function getModelLabel(): string
    {
        return __('Staff Member');
    }
}
