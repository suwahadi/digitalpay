<?php

namespace App\Filament\Resources;

use App\Models\ProductVariant;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $modelLabel = 'Varian Produk';
    protected static ?string $pluralModelLabel = 'Varian Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Varian')->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Varian')
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Toggle::make('is_active')->label('Aktif')->default(true),
                        Toggle::make('auto_delivery')->label('Auto Delivery')->default(false),
                    ])->columns(3),
                    Section::make('Harga & Stok')->schema([
                        TextInput::make('price')
                            ->label('Harga Promo')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('original_price')
                            ->label('Harga Asli')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('stock_quantity')
                            ->label('Jumlah Stok')
                            ->numeric()
                            ->helperText('Kosongkan untuk stok unlimited'),
                        TextInput::make('duration_days')
                            ->label('Durasi (Hari)')
                            ->numeric()
                            ->helperText('Kosongkan untuk lifetime'),
                    ])->columns(2),
                    Section::make('Digital Delivery')->schema([
                        TextInput::make('max_downloads')
                            ->label('Maksimal Download')
                            ->numeric()
                            ->helperText('Kosongkan untuk unlimited'),
                        TextInput::make('download_expiry_hours')
                            ->label('Expired Download (Jam)')
                            ->numeric()
                            ->helperText('Kosongkan untuk tidak expired'),
                        Textarea::make('delivery_template')
                            ->label('Template Delivery')
                            ->rows(4)
                            ->helperText('Template pesan yang akan dikirim saat delivery'),
                    ])->columns(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $html = '<div>' . $record->product->name . '</div>';
                        if ($record->name) {
                            $html .= '<div class="text-xs text-gray-500">' . $record->name . '</div>';
                        }
                        return $html;
                    })
                    ->html(),
                TextColumn::make('formatted_price')
                    ->label('Harga')
                    ->sortable('price')
                    ->formatStateUsing(function ($record) {
                        $html = '';
                        if ($record->original_price && $record->original_price > $record->price) {
                            $html .= '<div class="text-xs text-gray-500 line-through">' . $record->formatted_original_price . '</div>';
                        }
                        $html .= '<div>' . $record->formatted_price . '</div>';
                        return $html;
                    })
                    ->html(),
                TextColumn::make('discount_percentage')->label('Diskon')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '-')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('stock_status')->label('Stok')->badge()
                    ->color(function ($record) {
                        if ($record->stock_quantity === null) return 'info';
                        if ($record->stock_quantity > 10) return 'success';
                        if ($record->stock_quantity > 0) return 'warning';
                        return 'danger';
                    }),
                //TextColumn::make('duration_text')->label('Durasi'),
                TextColumn::make('is_active')->label('Aktif')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('auto_delivery')->label('Auto Delivery')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
                TextColumn::make('created_at')->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif'),
                Tables\Filters\TernaryFilter::make('auto_delivery')->label('Auto Delivery')
                    ->trueLabel('Ya')->falseLabel('Tidak'),
                // Tables\Filters\Filter::make('in_stock')
                //     ->label('Tersedia')
                //     ->query(fn ($query) => $query->inStock()),
                // Tables\Filters\Filter::make('out_of_stock')
                //     ->label('Habis')
                //     ->query(fn ($query) => $query->where('stock_quantity', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->modifyQueryUsing(function ($query) {
                return $query->join('products', 'product_variants.product_id', '=', 'products.id')
                           ->orderBy('products.name', 'asc')
                           ->orderBy('product_variants.sort_order', 'asc')
                           ->orderBy('product_variants.id', 'asc')
                           ->select('product_variants.*');
            })
            ->defaultSort('sort_order', 'asc')
            ->searchPlaceholder('Cari')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ProductVariantResource\Pages\ListProductVariants::route('/'),
            'create' => ProductVariantResource\Pages\CreateProductVariant::route('/create'),
            'edit' => ProductVariantResource\Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}