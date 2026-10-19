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
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class DigitalStockResource extends Resource
{
    protected static ?string $model = DigitalStock::class;
    protected static ?string $navigationGroup = 'Digital';
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $modelLabel = 'Digital Stock';
    protected static ?string $pluralModelLabel = 'Digital Stock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Stock')->schema([
                        Select::make('product_variant_id')
                            ->label('Varian Produk')
                            ->relationship('productVariant', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name . ' - ' . $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('stock_name')
                            ->label('Nama Stock')
                            ->required()
                            ->helperText('Nama identifikasi untuk stock ini'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                DigitalStock::STATUS_AVAILABLE => 'Available',
                                DigitalStock::STATUS_RESERVED => 'Reserved',
                                DigitalStock::STATUS_DELIVERED => 'Delivered',
                                DigitalStock::STATUS_EXPIRED => 'Expired',
                            ])
                            ->default(DigitalStock::STATUS_AVAILABLE)
                            ->required(),
                    ])->columns(2),
                    Section::make('Data Stock')->schema([
                        KeyValue::make('stock_data')
                            ->label('Data Stock')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->helperText('Data yang akan dikirim saat delivery (contoh: username, password, link, dll)'),
                    ])->columns(1),
                    Section::make('Informasi Tambahan')->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
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
                    ->label('Nama Stock')
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
                        DigitalStock::STATUS_AVAILABLE => 'Available',
                        DigitalStock::STATUS_RESERVED => 'Reserved',
                        DigitalStock::STATUS_DELIVERED => 'Delivered',
                        DigitalStock::STATUS_EXPIRED => 'Expired',
                        default => ucfirst($state)
                    }),
                // TextColumn::make('assigned_at')
                //     ->label('Assigned At')
                //     ->dateTime('d M Y H:i')
                //     ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivered_at')
                    ->label('Delivered At')
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
                        DigitalStock::STATUS_AVAILABLE => 'Available',
                        DigitalStock::STATUS_RESERVED => 'Reserved',
                        DigitalStock::STATUS_DELIVERED => 'Delivered',
                        DigitalStock::STATUS_EXPIRED => 'Expired',
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