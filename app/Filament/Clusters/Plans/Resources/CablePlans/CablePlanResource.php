<?php

namespace App\Filament\Clusters\Plans\Resources\CablePlans;

use App\Filament\Clusters\Plans\PlansCluster;
use App\Filament\Clusters\Plans\Resources\CablePlans\Pages\CreateCablePlan;
use App\Filament\Clusters\Plans\Resources\CablePlans\Pages\EditCablePlan;
use App\Filament\Clusters\Plans\Resources\CablePlans\Pages\ListCablePlans;
use App\Filament\Clusters\Plans\Resources\CablePlans\Schemas\CablePlanForm;
use App\Filament\Clusters\Plans\Resources\CablePlans\Tables\CablePlansTable;
use App\Models\CablePlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CablePlanResource extends Resource
{
    protected static ?string $model = CablePlan::class;

    protected static ?string $cluster = PlansCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTv;

    public static function form(Schema $schema): Schema
    {
        return CablePlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CablePlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCablePlans::route('/'),
            'create' => CreateCablePlan::route('/create'),
            'edit' => EditCablePlan::route('/{record}/edit'),
        ];
    }
}
