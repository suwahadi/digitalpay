<?php

namespace App\Filament\Resources;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $modelLabel = 'Order Item';
    protected static ?string $pluralModelLabel = 'Order Item';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Item')->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_variant_id')
                            ->label('Varian Produk')
                            ->relationship('productVariant', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])->columns(2),
                    Section::make('Data Produk')->schema([
                        TextInput::make('product_name')
                            ->label('Nama Produk')
                            ->required(),
                        TextInput::make('variant_name')
                            ->label('Nama Varian'),
                        Select::make('delivery_status')
                            ->label('Status Delivery')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'delivered' => 'Delivered',
                                'failed' => 'Failed',
                            ])
                            ->default('pending'),
                    ])->columns(2),
                    Section::make('Harga')->schema([
                        TextInput::make('price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ])->columns(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('product_name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $html = '<div>' . $record->product_name . '</div>';
                        if ($record->variant_name) {
                            $html .= '<div class="text-xs text-gray-500">' . $record->variant_name . '</div>';
                        }
                        return $html;
                    })
                    ->html(),
                // TextColumn::make('quantity')
                //     ->label('Qty')
                //     ->sortable(),
                TextColumn::make('formatted_price')
                    ->label('Harga')
                    ->sortable('price'),
                // TextColumn::make('formatted_total')
                //     ->label('Total')
                //     ->sortable('total'),
                TextColumn::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        default => 'gray'
                    }),
                // TextColumn::make('order.customer_name')
                //     ->label('Customer')
                //     ->searchable()
                //     ->limit(20),
                TextColumn::make('order.status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray'
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_id')
                    ->label('Order')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('delivery_status')
                    ->label('Status Delivery')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => OrderItemResource\Pages\ListOrderItems::route('/'),
            'create' => OrderItemResource\Pages\CreateOrderItem::route('/create'),
            'view' => OrderItemResource\Pages\ViewOrderItem::route('/{record}'),
            'edit' => OrderItemResource\Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }
}