<?php

namespace App\Filament\Clusters\Plans\Resources\AirtimePlans;

use App\Filament\Clusters\Plans\PlansCluster;
use App\Filament\Clusters\Plans\Resources\AirtimePlans\Pages\CreateAirtimePlan;
use App\Filament\Clusters\Plans\Resources\AirtimePlans\Pages\EditAirtimePlan;
use App\Filament\Clusters\Plans\Resources\AirtimePlans\Pages\ListAirtimePlans;
use App\Filament\Clusters\Plans\Resources\AirtimePlans\Schemas\AirtimePlanForm;
use App\Filament\Clusters\Plans\Resources\AirtimePlans\Tables\AirtimePlansTable;
use App\Models\AirtimePlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AirtimePlanResource extends Resource
{
    protected static ?string $model = AirtimePlan::class;

    protected static ?string $cluster = PlansCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    public static function form(Schema $schema): Schema
    {
        return AirtimePlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AirtimePlansTable::configure($table);
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
            'index' => ListAirtimePlans::route('/'),
            'create' => CreateAirtimePlan::route('/create'),
            'edit' => EditAirtimePlan::route('/{record}/edit'),
        ];
    }
}
