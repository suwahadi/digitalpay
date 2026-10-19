<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderItems extends ListRecords
{
    protected static string $resource = OrderItemResource::class;
    protected static ?string $title = 'Order Item';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}