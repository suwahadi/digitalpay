<?php

namespace App\Filament\Resources;

use App\Models\DigitalDelivery;
use App\Models\OrderItem;
use App\Models\DigitalStock;
use App\Models\GuestUser;
use App\Notifications\ManualDeliveryCompletedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class DigitalDeliveryResource extends Resource
{
    protected static ?string $model = DigitalDelivery::class;
    protected static ?string $navigationGroup = 'Digital';
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $modelLabel = 'Digital Delivery';
    protected static ?string $pluralModelLabel = 'Digital Delivery';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Delivery')->schema([
                        Select::make('order_item_id')
                            ->label('Order Item')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => 
                                \App\Models\OrderItem::query()
                                    ->with(['order', 'productVariant'])
                                    ->whereHas('order', function ($query) use ($search) {
                                        $query->where('order_number', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('productVariant', function ($query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn ($record) => [
                                        $record->id => "{$record->order->order_number} - {$record->productVariant->name}"
                                    ])
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(fn ($value): ?string => 
                                ($record = \App\Models\OrderItem::with(['order', 'productVariant'])->find($value))
                                    ? "{$record->order->order_number} - {$record->productVariant->name}"
                                    : null
                            )
                            ->required(),
                        Select::make('digital_stock_id')
                            ->label('Digital Stock')
                            ->relationship('digitalStock', 'stock_name')
                            ->searchable()
                            ->preload(),
                        Select::make('delivery_status')
                            ->label('Status Delivery')
                            ->options([
                                DigitalDelivery::STATUS_PENDING => 'Pending',
                                DigitalDelivery::STATUS_PROCESSING => 'Processing',
                                DigitalDelivery::STATUS_COMPLETED => 'Completed',
                                DigitalDelivery::STATUS_FAILED => 'Failed',
                            ])
                            ->default(DigitalDelivery::STATUS_PENDING)
                            ->helperText('Pilih "Pending" untuk delivery manual baru. Status akan otomatis diubah ke "Completed" setelah disimpan.')
                            ->required(),
                        Select::make('delivery_trigger')
                            ->label('Trigger')
                            ->options([
                                'auto' => 'Auto',
                                'manual' => 'Manual',
                            ])
                            ->default('manual')
                            ->helperText('Pilih "Manual" untuk mengirim email notifikasi dengan konten delivery kepada customer.')
                            ->required(),
                    ])->columns(2),
                    Section::make('Konfigurasi Download')->schema([
                        TextInput::make('max_downloads')
                            ->label('Max Downloads')
                            ->numeric()
                            ->default(3),
                        TextInput::make('download_count')
                            ->label('Download Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        DateTimePicker::make('expires_at')
                            ->label('Expired At')
                            ->required(),
                        Toggle::make('email_sent')
                            ->label('Email Sent')
                            ->default(false),
                    ])->columns(2),
                    Section::make('Konten Delivery')->schema([
                        Textarea::make('delivery_content')
                            ->label('Konten Delivery')
                            ->rows(6)
                            ->columnSpanFull()
                            ->required()
                            ->helperText('Konten ini akan dikirim ke email customer. Format: Username: xxx, Password: xxx, Durasi: xxx, dll.')
                            ->placeholder("Contoh:\nUsername: ytprem12000@gmail.com\nPassword: Maju&Sukses$123\nDurasi: 90 days"),
                        Textarea::make('manual_notes')
                            ->label('Catatan Manual (Internal)')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Catatan internal untuk Admin, tidak dikirim ke customer'),
                    ])->columns(1),
                    Section::make('Informasi Waktu')->schema([
                        DateTimePicker::make('delivered_at')
                            ->label('Delivered At'),
                        DateTimePicker::make('first_download_at')
                            ->label('First Download At')
                            ->disabled(),
                        DateTimePicker::make('last_download_at')
                            ->label('Last Download At')
                            ->disabled(),
                        DateTimePicker::make('email_sent_at')
                            ->label('Email Sent At')
                            ->disabled(),
                    ])->columns(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orderItem.order.order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orderItem.productVariant.name')
                    ->label('Produk Varian')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('digitalStock.stock_name')
                    ->label('Stock')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        DigitalDelivery::STATUS_PENDING => 'warning',
                        DigitalDelivery::STATUS_PROCESSING => 'info',
                        DigitalDelivery::STATUS_COMPLETED => 'success',
                        DigitalDelivery::STATUS_FAILED => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        DigitalDelivery::STATUS_PENDING => 'Pending',
                        DigitalDelivery::STATUS_PROCESSING => 'Processing',
                        DigitalDelivery::STATUS_COMPLETED => 'Completed',
                        DigitalDelivery::STATUS_FAILED => 'Failed',
                        default => ucfirst($state)
                    }),
                TextColumn::make('delivery_trigger')
                    ->label('Trigger')
                    ->badge()
                    ->color(fn ($state) => $state === 'manual' ? 'warning' : 'info')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('download_count')
                    ->label('Downloads')
                    ->formatStateUsing(fn ($record) => "{$record->download_count}/{$record->max_downloads}"),
                // TextColumn::make('remaining_downloads')
                //     ->label('Remaining')
                //     ->badge()
                //     ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('email_sent')
                    ->label('Email')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Sent' : 'Not Sent')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_status')
                    ->label('Status Delivery')
                    ->options([
                        DigitalDelivery::STATUS_PENDING => 'Pending',
                        DigitalDelivery::STATUS_PROCESSING => 'Processing',
                        DigitalDelivery::STATUS_COMPLETED => 'Completed',
                        DigitalDelivery::STATUS_FAILED => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('delivery_trigger')
                    ->label('Trigger')
                    ->options([
                        'auto' => 'Auto',
                        'manual' => 'Manual',
                    ]),
                Tables\Filters\TernaryFilter::make('email_sent')
                    ->label('Email Status')
                    ->trueLabel('Sent')
                    ->falseLabel('Not Sent'),
                // Tables\Filters\Filter::make('expired')
                //     ->label('Expired')
                //     ->query(fn ($query) => $query->where('expires_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resend_email')
                    ->label('Kirim Ulang Email')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Ulang Email Delivery')
                    ->modalDescription('Email notifikasi dengan konten delivery akan dikirim ulang ke customer. Pastikan queue worker sedang berjalan.')
                    ->modalSubmitActionLabel('Ya, Kirim Ulang')
                    ->visible(fn ($record) => $record->delivery_trigger === 'manual' && !empty($record->delivery_content))
                    ->action(function ($record) {
                        // Reset email_sent flag
                        $record->updateQuietly([
                            'email_sent' => false,
                            'email_sent_at' => null,
                        ]);
                        
                        // Get order and send notification
                        $order = $record->orderItem->order;
                        $notifiable = $order->user_id 
                            ? $order->user 
                            : new \App\Models\GuestUser($order->customer_email, $order->customer_name, $order->customer_phone);
                        
                        $notifiable->notify(new \App\Notifications\ManualDeliveryCompletedNotification($order));
                        
                        // Update email_sent after notification queued
                        $record->updateQuietly([
                            'email_sent' => true,
                            'email_sent_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Email Dikirim Ulang')
                            ->body('Email notifikasi telah diqueue ulang dan akan dikirim dalam beberapa saat.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => DigitalDeliveryResource\Pages\ListDigitalDeliveries::route('/'),
            'create' => DigitalDeliveryResource\Pages\CreateDigitalDelivery::route('/create'),
            'view' => DigitalDeliveryResource\Pages\ViewDigitalDelivery::route('/{record}'),
            'edit' => DigitalDeliveryResource\Pages\EditDigitalDelivery::route('/{record}/edit'),
        ];
    }
}