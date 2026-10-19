<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with(['items', 'paymentMethod'])->latest()->limit(12))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('items')
                    ->label('Produk')
                    ->formatStateUsing(function ($record) {
                        $items = $record->items;
                        $html = '';
                        foreach ($items as $index => $item) {
                            if ($index > 0) $html .= '<br>';
                            $html .= '<div class="font-medium">' . e($item->product_name) . '</div>';
                            if ($item->variant_name) {
                                $html .= '<div class="text-xs text-gray-500">' . e($item->variant_name) . '</div>';
                            }
                        }
                        return $html ?: '-';
                    })
                    ->html(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'paid' => 'Lunas',
                        'pending' => 'Menunggu',
                        'cancelled' => 'Dibatalkan',
                        'expired' => 'Kedaluwarsa',
                        default => ucfirst($state)
                    }),
                TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->url(fn (Order $record): string => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->heading('Order Terbaru')
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->headerActions([
                Action::make('viewAll')
                    ->label('Selengkapnya')
                    ->url(\App\Filament\Resources\OrderResource::getUrl('index'))
                    ->icon('heroicon-o-arrow-right')
                    ->color('primary'),
            ]);
    }
}
