<?php

namespace App\Filament\Resources\DigitalStockResource\Pages;

use App\Filament\Resources\DigitalStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDigitalStock extends EditRecord
{
    protected static string $resource = DigitalStockResource::class;
    protected static ?string $title = 'Ubah Digital Stock';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat'),
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }
}