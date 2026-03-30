<?php

namespace App\Filament\Resources\Dividends;

use App\Filament\Resources\Dividends\Pages\ListDividends;
use App\Filament\Resources\Dividends\Schemas\DividendForm;
use App\Filament\Resources\Dividends\Tables\DividendsTable;
use App\Models\Dividend;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DividendResource extends Resource
{
    protected static ?string $model = Dividend::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Shares';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DividendForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DividendsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDividends::route('/'),
        ];
    }
}
