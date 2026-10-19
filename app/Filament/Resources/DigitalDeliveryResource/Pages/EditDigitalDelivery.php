<?php

namespace App\Filament\Resources\DigitalDeliveryResource\Pages;

use App\Filament\Resources\DigitalDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalDelivery extends EditRecord
{
    protected static string $resource = DigitalDeliveryResource::class;
    protected static ?string $title = 'Ubah Digital Delivery';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat'),
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }
}