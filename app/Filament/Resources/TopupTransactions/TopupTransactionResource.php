<?php

namespace App\Filament\Resources\TopupTransactions;

use App\Filament\Resources\TopupTransactions\Pages\ListTopupTransactions;
use App\Filament\Resources\TopupTransactions\Pages\ViewTopupTransaction;
use App\Filament\Resources\TopupTransactions\Schemas\TopupTransactionInfolist;
use App\Filament\Resources\TopupTransactions\Tables\TopupTransactionsTable;
use App\Models\TopupTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TopupTransactionResource extends Resource
{
    protected static ?string $model = TopupTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::DevicePhoneMobile;

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Topup History';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TopupTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TopupTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTopupTransactions::route('/'),
            'view' => ViewTopupTransaction::route('/{record}'),
        ];
    }
}
