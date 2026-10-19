<?php

namespace App\Filament\Resources\DigitalStockResource\Pages;

use App\Filament\Resources\DigitalStockResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDigitalStock extends CreateRecord
{
    protected static string $resource = DigitalStockResource::class;
    protected static ?string $title = 'Tambah Digital Stock';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['added_at'] = now();
        $data['added_by_user_id'] = Auth::id();
        
        return $data;
    }
}