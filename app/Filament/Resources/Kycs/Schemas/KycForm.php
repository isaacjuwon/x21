<?php

namespace App\Filament\Resources\Kycs\Schemas;

use App\Enums\Kyc\KycMethod;
use App\Enums\Kyc\KycStatus;
use App\Enums\Kyc\KycType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KycForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Verification Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->required()
                                ->disabledOn('edit'),
                            Select::make('type')
                                ->options(KycType::class)
                                ->required()
                                ->disabledOn('edit'),
                            TextInput::make('number')
                                ->label('Verification Number')
                                ->required(),
                            Select::make('method')
                                ->options(KycMethod::class)
                                ->required(),
                            Select::make('status')
                                ->options(KycStatus::class)
                                ->required(),
                            DateTimePicker::make('verified_at')
                                ->label('Verified Date'),
                        ]),
                    Textarea::make('rejection_reason')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make('Attachments')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('Document File')
                        ->directory('kyc')
                        ->visibility('public')
                        ->openable()
                        ->downloadable(),
                ]),
        ]);
    }
}
