<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Order';
    protected static ?string $pluralModelLabel = 'Order';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Order')->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                    ])->columns(2),
                    Section::make('Data Customer')->schema([
                        TextInput::make('customer_name')
                            ->label('Nama Customer')
                            ->required(),
                        TextInput::make('customer_email')
                            ->label('Email Customer')
                            ->email()
                            ->required(),
                        TextInput::make('customer_phone')
                            ->label('Telepon Customer'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ])->columns(2),
                    Section::make('Transaksi')->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('fee_amount')
                            ->label('Biaya Admin')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Expired Pada'),
                    ])->columns(2),
                    Section::make('Manual Delivery')->schema([
                        Toggle::make('needs_manual_delivery')
                            ->label('Perlu Manual Delivery'),
                        Select::make('delivery_method')
                            ->label('Metode Delivery')
                            ->options([
                                'auto' => 'Auto',
                                'manual' => 'Manual',
                                'mixed' => 'Mixed',
                            ]),
                        DateTimePicker::make('manual_delivery_queued_at')
                            ->label('Antrian Manual'),
                        DateTimePicker::make('manual_processed_at')
                            ->label('Diproses Manual'),
                    ])->columns(2),
                    Section::make('Midtrans Data')->schema([
                        TextInput::make('midtrans_order_id')
                            ->label('Midtrans Order ID'),
                        TextInput::make('midtrans_transaction_id')
                            ->label('Transaction ID'),
                        TextInput::make('midtrans_snap_token')
                            ->label('Snap Token'),
                        DateTimePicker::make('midtrans_paid_at')
                            ->label('Midtrans Paid At'),
                    ])->columns(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                // TextColumn::make('customer_email')
                //     ->label('Email')
                //     ->searchable()
                //     ->limit(30),
                TextColumn::make('formatted_total_amount')
                    ->label('Total')
                    ->searchable('total_amount')
                    ->sortable('total_amount'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray'
                    }),
                // TextColumn::make('payment_status')
                //     ->label('Pembayaran')
                //     ->badge()
                //     ->color(fn ($state) => match($state) {
                //         'unpaid' => 'gray',
                //         'pending' => 'warning',
                //         'paid' => 'success',
                //         'cancelled' => 'danger',
                //         'expired' => 'gray',
                //         'failed' => 'danger',
                //         default => 'gray'
                //     }),
                TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),
                // TextColumn::make('delivery_method')
                //     ->label('Delivery')
                //     ->badge()
                //     ->color(fn ($state) => match($state) {
                //         'auto' => 'success',
                //         'manual' => 'warning',
                //         'mixed' => 'info',
                //         default => 'gray'
                //     }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('needs_manual_delivery')
                    ->label('Manual Delivery')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_items')
                    ->label('Items')
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn ($record) => \App\Filament\Resources\OrderItemResource::getUrl('index', ['tableFilters' => ['order_id' => ['value' => $record->id]]])),
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
            'index' => OrderResource\Pages\ListOrders::route('/'),
            'create' => OrderResource\Pages\CreateOrder::route('/create'),
            'view' => OrderResource\Pages\ViewOrder::route('/{record}'),
            'edit' => OrderResource\Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}