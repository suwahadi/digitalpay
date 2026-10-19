<?php

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Resources\Pages\EditRecord;

class EditProductVariant extends EditRecord
{
    protected static string $resource = ProductVariantResource::class;
    protected static ?string $title = 'Ubah Varian Produk';
}