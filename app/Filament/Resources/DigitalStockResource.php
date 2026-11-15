<?php

namespace App\Filament\Resources;

use App\Models\DigitalStock;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class DigitalStockResource extends Resource
{
    protected static ?string $model = DigitalStock::class;
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $modelLabel = 'Stok Digital';
    protected static ?string $pluralModelLabel = 'Stok Digital';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Stok')->schema([
                        Select::make('product_variant_id')
                            ->label('Varian Produk')
                            ->relationship('productVariant', 'name', fn ($query) => $query->where('delivery_type', ProductVariant::DELIVERY_AUTO))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name . ' - ' . $record->name)
                            ->helperText('Hanya varian dengan pengiriman Otomatis')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('stock_name')
                            ->label('Nama Stok')
                            ->required()
                            ->helperText('Penanda internal, mis. Akun 01'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                DigitalStock::STATUS_AVAILABLE => 'Tersedia',
                                DigitalStock::STATUS_RESERVED => 'Dipesan',
                                DigitalStock::STATUS_DELIVERED => 'Terkirim',
                                DigitalStock::STATUS_EXPIRED => 'Kedaluwarsa',
                            ])
                            ->default(DigitalStock::STATUS_AVAILABLE)
                            ->required(),
                    ])->columns(2),
                    Section::make('Data Akun')->schema([
                        KeyValue::make('stock_data')
                            ->label('Data Akun')
                            ->keyLabel('Nama Kolom')
                            ->valueLabel('Isi')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Dikirim ke pembeli, mis. username, password, link, PIN'),
                        DateTimePicker::make('expires_at')
                            ->label('Berlaku Sampai')
                            ->helperText('Opsional. Lewat dari tanggal ini, stok tidak akan dikirim'),
                    ])->columns(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productVariant.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $html = '<div class="font-medium">' . $record->productVariant->product->name . '</div>';
                        if ($record->productVariant->name) {
                            $html .= '<div class="text-xs text-gray-500">↳ ' . $record->productVariant->name . '</div>';
                        }
                        return $html;
                    })
                    ->html(),
                TextColumn::make('stock_name')
                    ->label('Nama Stok')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        DigitalStock::STATUS_AVAILABLE => 'success',
                        DigitalStock::STATUS_RESERVED => 'warning',
                        DigitalStock::STATUS_DELIVERED => 'info',
                        DigitalStock::STATUS_EXPIRED => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        DigitalStock::STATUS_AVAILABLE => 'Tersedia',
                        DigitalStock::STATUS_RESERVED => 'Dipesan',
                        DigitalStock::STATUS_DELIVERED => 'Terkirim',
                        DigitalStock::STATUS_EXPIRED => 'Kedaluwarsa',
                        default => ucfirst($state)
                    }),

                TextColumn::make('delivered_at')
                    ->label('Terkirim')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_variant_id')
                    ->label('Varian Produk')
                    ->relationship('productVariant', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name . ' - ' . $record->name)
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        DigitalStock::STATUS_AVAILABLE => 'Tersedia',
                        DigitalStock::STATUS_RESERVED => 'Dipesan',
                        DigitalStock::STATUS_DELIVERED => 'Terkirim',
                        DigitalStock::STATUS_EXPIRED => 'Kedaluwarsa',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->modifyQueryUsing(function ($query) {
                return $query->join('product_variants', 'digital_stocks.product_variant_id', '=', 'product_variants.id')
                           ->join('products', 'product_variants.product_id', '=', 'products.id')
                           ->orderBy('products.name', 'asc')
                           ->orderBy('product_variants.sort_order', 'asc')
                           ->orderBy('product_variants.id', 'asc')
                           ->select('digital_stocks.*');
            })
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => DigitalStockResource\Pages\ListDigitalStocks::route('/'),
            'create' => DigitalStockResource\Pages\CreateDigitalStock::route('/create'),
            'view' => DigitalStockResource\Pages\ViewDigitalStock::route('/{record}'),
            'edit' => DigitalStockResource\Pages\EditDigitalStock::route('/{record}/edit'),
        ];
    }
}