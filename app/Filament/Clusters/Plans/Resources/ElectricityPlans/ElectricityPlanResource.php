<?php

namespace App\Filament\Clusters\Plans\Resources\ElectricityPlans;

use App\Filament\Clusters\Plans\PlansCluster;
use App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages\CreateElectricityPlan;
use App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages\EditElectricityPlan;
use App\Filament\Clusters\Plans\Resources\ElectricityPlans\Pages\ListElectricityPlans;
use App\Filament\Clusters\Plans\Resources\ElectricityPlans\Schemas\ElectricityPlanForm;
use App\Filament\Clusters\Plans\Resources\ElectricityPlans\Tables\ElectricityPlansTable;
use App\Models\ElectricityPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ElectricityPlanResource extends Resource
{
    protected static ?string $model = ElectricityPlan::class;

    protected static ?string $cluster = PlansCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    public static function form(Schema $schema): Schema
    {
        return ElectricityPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectricityPlansTable::configure($table);
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
            'index' => ListElectricityPlans::route('/'),
            'create' => CreateElectricityPlan::route('/create'),
            'edit' => EditElectricityPlan::route('/{record}/edit'),
        ];
    }
}
