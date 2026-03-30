<?php

namespace App\Filament\Resources\ShareOrders;

use App\Filament\Resources\ShareOrders\Pages\ListShareOrders;
use App\Filament\Resources\ShareOrders\Pages\ViewShareOrder;
use App\Filament\Resources\ShareOrders\Schemas\ShareOrderForm;
use App\Filament\Resources\ShareOrders\Schemas\ShareOrderInfolist;
use App\Filament\Resources\ShareOrders\Tables\ShareOrdersTable;
use App\Models\ShareOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShareOrderResource extends Resource
{
    protected static ?string $model = ShareOrder::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static string|\UnitEnum|null $navigationGroup = 'Shares';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ShareOrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShareOrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShareOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShareOrders::route('/'),
            'view' => ViewShareOrder::route('/{record}'),
        ];
    }
}
