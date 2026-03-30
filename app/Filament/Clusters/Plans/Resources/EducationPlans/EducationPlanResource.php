<?php

namespace App\Filament\Clusters\Plans\Resources\EducationPlans;

use App\Filament\Clusters\Plans\PlansCluster;
use App\Filament\Clusters\Plans\Resources\EducationPlans\Pages\CreateEducationPlan;
use App\Filament\Clusters\Plans\Resources\EducationPlans\Pages\EditEducationPlan;
use App\Filament\Clusters\Plans\Resources\EducationPlans\Pages\ListEducationPlans;
use App\Filament\Clusters\Plans\Resources\EducationPlans\Schemas\EducationPlanForm;
use App\Filament\Clusters\Plans\Resources\EducationPlans\Tables\EducationPlansTable;
use App\Models\EducationPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EducationPlanResource extends Resource
{
    protected static ?string $model = EducationPlan::class;

    protected static ?string $cluster = PlansCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function form(Schema $schema): Schema
    {
        return EducationPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationPlansTable::configure($table);
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
            'index' => ListEducationPlans::route('/'),
            'create' => CreateEducationPlan::route('/create'),
            'edit' => EditEducationPlan::route('/{record}/edit'),
        ];
    }
}
