<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\DigitalDeliveryService;
use App\Services\QrisgatePaymentSync;
use Filament\Notifications\Notification;
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
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Pesanan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Pesanan')->schema([
                        TextInput::make('order_number')->label('Nomor Pesanan')->disabled(),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->disabled(),
                        Select::make('status')
                            ->label('Status')
                            ->options(static::statusOptions())
                            ->required(),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options(static::paymentStatusOptions())
                            ->required(),
                        DateTimePicker::make('expires_at')->label('Batas Bayar')->disabled(),
                        DateTimePicker::make('midtrans_paid_at')->label('Dibayar Pada')->disabled(),
                    ])->columns(2),
                    Section::make('Pembeli')->schema([
                        TextInput::make('customer_name')->label('Nama')->required(),
                        TextInput::make('customer_email')->label('Email')->email()->required(),
                        TextInput::make('customer_phone')->label('WhatsApp'),
                        Textarea::make('notes')->label('Catatan Pembeli')->rows(2)->columnSpanFull(),
                    ])->columns(3),
                    Section::make('Rincian Biaya')->schema([
                        TextInput::make('subtotal')->label('Subtotal')->prefix('Rp')->disabled(),
                        TextInput::make('discount_amount')->label('Diskon Voucher')->prefix('Rp')->disabled(),
                        TextInput::make('fee_amount')->label('Biaya Layanan')->prefix('Rp')->disabled(),
                        TextInput::make('unique_code')->label('Kode Unik')->prefix('Rp')->disabled(),
                        TextInput::make('total_amount')->label('Total')->prefix('Rp')->disabled(),
                    ])->columns(5),
                    Section::make('Pengiriman Manual')->schema([
                        Toggle::make('needs_manual_delivery')->label('Perlu Dikirim Manual'),
                        DateTimePicker::make('manual_processed_at')->label('Selesai Dikirim')->disabled(),
                        Textarea::make('manual_notes')->label('Catatan Admin')->rows(2)->columnSpanFull(),
                    ])->columns(2),
                ]),
            ]);
    }

    public static function statusOptions(): array
    {
        return ['pending' => 'Menunggu', 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa'];
    }

    public static function paymentStatusOptions(): array
    {
        return ['unpaid' => 'Belum Dibayar', 'pending' => 'Menunggu', 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa', 'failed' => 'Gagal'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::pendingManualDelivery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pesanan lunas yang perlu dikirim manual';
    }

    public static function canDeliverManually(Order $order): bool
    {
        return $order->status === 'paid'
            && app(DigitalDeliveryService::class)->pendingManualItems($order)->isNotEmpty();
    }

    public static function manualDeliveryForm(): array
    {
        return [
            Textarea::make('content')
                ->label('Isi Pengiriman')
                ->helperText('Data akun / link / instruksi yang diterima pembeli. Tampil di invoice dan dikirim lewat email.')
                ->rows(6)
                ->required(),
            Textarea::make('notes')->label('Catatan Internal')->rows(2),
        ];
    }

    public static function deliverManually(Order $order, array $data): void
    {
        $service = app(DigitalDeliveryService::class);
        $items = $service->pendingManualItems($order);

        foreach ($items as $item) {
            $service->deliverManually($item, $data['content'], auth()->id(), $data['notes'] ?? null);
        }

        Notification::make()
            ->title('Produk terkirim ke pembeli')
            ->body($items->count().' item ditandai terkirim, email pengiriman sedang dikirim.')
            ->success()
            ->send();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Nomor Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderItems.variant_name')
                    ->label('Produk')
                    ->formatStateUsing(fn (Order $record) => $record->orderItems->map(fn ($item) => $item->product_name.' - '.$item->variant_name)->join(', '))
                    ->limit(40),
                TextColumn::make('formatted_total_amount')
                    ->label('Total')
                    ->sortable('total_amount'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::statusOptions()[$state] ?? ucfirst((string) $state))
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('delivery_state')
                    ->label('Pengiriman')
                    ->badge()
                    ->state(fn (Order $record) => match (true) {
                        $record->status !== 'paid' => '-',
                        (bool) $record->needs_manual_delivery => 'Perlu Dikirim',
                        default => 'Terkirim',
                    })
                    ->color(fn ($state) => match ($state) {
                        'Perlu Dikirim' => 'warning',
                        'Terkirim' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('paymentMethod.name')
                    ->label('Metode')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('needs_delivery')
                    ->label('Perlu Dikirim')
                    ->query(fn ($query) => $query->pendingManualDelivery()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::statusOptions()),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(static::paymentStatusOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('deliver_manual')
                    ->label('Kirim Manual')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (Order $record) => static::canDeliverManually($record))
                    ->form(static::manualDeliveryForm())
                    ->modalHeading(fn (Order $record) => 'Kirim Manual - '.$record->order_number)
                    ->modalSubmitActionLabel('Kirim ke Pembeli')
                    ->action(fn (Order $record, array $data) => static::deliverManually($record, $data)),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Tandai Lunas')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record) => static::canMarkPaid($record))
                    ->requiresConfirmation()
                    ->modalHeading('Tandai pesanan lunas?')
                    ->modalDescription(fn (Order $record) => static::markPaidDescription($record))
                    ->modalSubmitActionLabel('Ya, tandai lunas')
                    ->action(fn (Order $record) => static::markPaid($record)),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('orderItems'))
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getRelations(): array
    {
        return [
            OrderResource\RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function canMarkPaid(Order $order): bool
    {
        return $order->isQrisgate() && $order->status !== 'paid';
    }

    public static function markPaidDescription(Order $order): string
    {
        return 'Pastikan dana '.$order->formatted_total_amount.' sudah masuk ke rekening QRIS. Email pembayaran berhasil dan pengiriman produk akan diproses otomatis.';
    }

    public static function markPaid(Order $order): void
    {
        $user = auth()->user();
        $done = app(QrisgatePaymentSync::class)->markPaidByAdmin($order, $user?->id, $user?->name);

        if ($done) {
            Notification::make()->title('Pesanan ditandai lunas')->body('Pengiriman produk sedang diproses.')->success()->send();

            return;
        }

        Notification::make()->title('Pesanan tidak dapat ditandai lunas')->warning()->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => OrderResource\Pages\ListOrders::route('/'),
            'view' => OrderResource\Pages\ViewOrder::route('/{record}'),
            'edit' => OrderResource\Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}