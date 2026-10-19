<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderItem extends ViewRecord
{
    protected static string $resource = OrderItemResource::class;
    protected static ?string $title = 'Lihat Order Item';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Ubah'),
        ];
    }
}