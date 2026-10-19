<?php

namespace App\Filament\Resources\DigitalStockResource\Pages;

use App\Filament\Resources\DigitalStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDigitalStock extends ViewRecord
{
    protected static string $resource = DigitalStockResource::class;
    protected static ?string $title = 'Lihat Digital Stock';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Ubah'),
        ];
    }
}