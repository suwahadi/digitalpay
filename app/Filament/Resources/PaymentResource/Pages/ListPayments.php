<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
    protected static ?string $title = 'Pembayaran';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}