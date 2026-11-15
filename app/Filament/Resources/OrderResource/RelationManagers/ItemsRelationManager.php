<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderItem;
use App\Models\ProductVariant;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    protected static ?string $title = 'Item & Pengiriman';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['productVariant', 'digitalDelivery']))
            ->columns([
                TextColumn::make('product_name')->label('Produk')
                    ->description(fn (OrderItem $record) => $record->variant_name),
                TextColumn::make('total')->label('Harga')->money('IDR', locale: 'id'),
                TextColumn::make('productVariant.delivery_type')->label('Metode')->badge()
                    ->formatStateUsing(fn ($state) => $state === ProductVariant::DELIVERY_AUTO ? 'Otomatis' : 'Manual')
                    ->color(fn ($state) => $state === ProductVariant::DELIVERY_AUTO ? 'info' : 'warning'),
                TextColumn::make('delivery_status')->label('Status Kirim')->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'delivered' => 'Terkirim',
                        'failed' => 'Gagal',
                        default => 'Belum Dikirim',
                    })
                    ->color(fn ($state) => match ($state) {
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('digitalDelivery.delivery_content')->label('Isi Pengiriman')
                    ->placeholder('-')
                    ->limit(60)
                    ->tooltip(fn (OrderItem $record) => $record->digitalDelivery?->delivery_content)
                    ->wrap(),
                TextColumn::make('digitalDelivery.delivered_at')->label('Dikirim')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
