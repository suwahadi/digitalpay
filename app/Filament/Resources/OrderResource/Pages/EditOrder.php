<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = 'Ubah Order';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()->label('Lihat'),
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }
}