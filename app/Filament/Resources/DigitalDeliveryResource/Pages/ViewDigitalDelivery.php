<?php

namespace App\Filament\Resources\DigitalDeliveryResource\Pages;

use App\Filament\Resources\DigitalDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDigitalDelivery extends ViewRecord
{
    protected static string $resource = DigitalDeliveryResource::class;
    protected static ?string $title = 'Lihat Digital Delivery';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Ubah'),
        ];
    }
}