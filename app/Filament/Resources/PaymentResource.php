<?php

namespace App\Filament\Resources;

use App\Models\Payment;
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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $modelLabel = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Pembayaran')->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'success' => 'Success',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                        TextInput::make('gateway_provider')
                            ->label('Gateway Provider'),
                    ])->columns(2),
                    Section::make('Data Transaksi')->schema([
                        TextInput::make('amount')
                            ->label('Jumlah')
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
                        TextInput::make('currency')
                            ->label('Currency')
                            ->default('IDR'),
                    ])->columns(2),
                    Section::make('Informasi Customer')->schema([
                        TextInput::make('customer_email')
                            ->label('Email Customer')
                            ->email(),
                        TextInput::make('customer_phone')
                            ->label('Telepon Customer'),
                        TextInput::make('gateway_transaction_id')
                            ->label('Transaction ID'),
                        TextInput::make('gateway_reference')
                            ->label('Reference'),
                    ])->columns(2),
                    Section::make('Midtrans Data')->schema([
                        TextInput::make('midtrans_snap_token')
                            ->label('Snap Token'),
                        TextInput::make('midtrans_transaction_status')
                            ->label('Transaction Status'),
                        TextInput::make('midtrans_payment_type')
                            ->label('Payment Type'),
                        TextInput::make('midtrans_gross_amount')
                            ->label('Gross Amount')
                            ->numeric()
                            ->prefix('Rp'),
                    ])->columns(2),
                    Section::make('Waktu')->schema([
                        DateTimePicker::make('paid_at')
                            ->label('Dibayar Pada'),
                        DateTimePicker::make('expired_at')
                            ->label('Expired Pada'),
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
                    ->sortable(),
                TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('formatted_total_amount')
                    ->label('Total')
                    ->searchable('total_amount')
                    ->sortable('total_amount'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'expired' => 'gray',
                        default => 'gray'
                    }),
                // TextColumn::make('gateway_provider')
                //     ->label('Gateway')
                //     ->badge()
                //     ->color('info'),
                // TextColumn::make('customer_email')
                //     ->label('Customer')
                //     ->searchable()
                //     ->limit(30),
                TextColumn::make('paid_at')
                    ->label('Dibayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                // Tables\Filters\SelectFilter::make('gateway_provider')
                //     ->label('Gateway')
                //     ->options([
                //         'midtrans' => 'Midtrans',
                //         'manual' => 'Manual',
                //     ]),
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Metode Pembayaran')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => PaymentResource\Pages\ListPayments::route('/'),
            'create' => PaymentResource\Pages\CreatePayment::route('/create'),
            'view' => PaymentResource\Pages\ViewPayment::route('/{record}'),
            'edit' => PaymentResource\Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}