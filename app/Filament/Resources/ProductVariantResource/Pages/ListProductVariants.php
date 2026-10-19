<?php

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductVariants extends ListRecords
{
    protected static string $resource = ProductVariantResource::class;
    protected static ?string $title = 'Varian Produk';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}