<?php

namespace App\Filament\Resources\DigitalStockResource\Pages;

use App\Filament\Resources\DigitalStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDigitalStocks extends ListRecords
{
    protected static string $resource = DigitalStockResource::class;
    protected static ?string $title = 'Digital Stock';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}