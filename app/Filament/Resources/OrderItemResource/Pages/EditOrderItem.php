<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderItem extends EditRecord
{
    protected static string $resource = OrderItemResource::class;
    protected static ?string $title = 'Ubah Order Item';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat'),
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }
}