<?php

namespace App\Filament\Resources\DigitalDeliveryResource\Pages;

use App\Filament\Resources\DigitalDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDigitalDeliveries extends ListRecords
{
    protected static string $resource = DigitalDeliveryResource::class;
    protected static ?string $title = 'Digital Delivery';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}