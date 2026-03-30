<?php

namespace App\Filament\Clusters\Plans\Resources\DataPlans;

use App\Filament\Clusters\Plans\PlansCluster;
use App\Filament\Clusters\Plans\Resources\DataPlans\Pages\CreateDataPlan;
use App\Filament\Clusters\Plans\Resources\DataPlans\Pages\EditDataPlan;
use App\Filament\Clusters\Plans\Resources\DataPlans\Pages\ListDataPlans;
use App\Filament\Clusters\Plans\Resources\DataPlans\Schemas\DataPlanForm;
use App\Filament\Clusters\Plans\Resources\DataPlans\Tables\DataPlansTable;
use App\Models\DataPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataPlanResource extends Resource
{
    protected static ?string $model = DataPlan::class;

    protected static ?string $cluster = PlansCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function form(Schema $schema): Schema
    {
        return DataPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataPlansTable::configure($table);
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
            'index' => ListDataPlans::route('/'),
            'create' => CreateDataPlan::route('/create'),
            'edit' => EditDataPlan::route('/{record}/edit'),
        ];
    }
}
