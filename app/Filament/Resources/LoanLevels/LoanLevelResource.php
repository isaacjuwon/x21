<?php

namespace App\Filament\Resources\LoanLevels;

use App\Filament\Resources\LoanLevels\Pages\CreateLoanLevel;
use App\Filament\Resources\LoanLevels\Pages\EditLoanLevel;
use App\Filament\Resources\LoanLevels\Pages\ListLoanLevels;
use App\Filament\Resources\LoanLevels\Schemas\LoanLevelForm;
use App\Filament\Resources\LoanLevels\Tables\LoanLevelsTable;
use App\Models\LoanLevel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoanLevelResource extends Resource
{
    protected static ?string $model = LoanLevel::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Loans';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Loan Levels';

    public static function form(Schema $schema): Schema
    {
        return LoanLevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanLevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoanLevels::route('/'),
            'create' => CreateLoanLevel::route('/create'),
            'edit' => EditLoanLevel::route('/{record}/edit'),
        ];
    }
}
