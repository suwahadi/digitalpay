<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\DigitalStockResource;
use App\Models\ProductVariant;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Varian';

    protected static ?string $modelLabel = 'Varian';

    protected static ?string $pluralModelLabel = 'Varian';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Varian')->schema([
                TextInput::make('name')->label('Nama Varian')->required()->maxLength(255),
                TextInput::make('duration_days')->label('Durasi (Hari)')->numeric()->minValue(1)->helperText('Kosongkan bila tanpa batas waktu'),
                TextInput::make('price')->label('Harga Jual')->numeric()->prefix('Rp')->required()->minValue(0),
                TextInput::make('original_price')->label('Harga Coret')->numeric()->prefix('Rp')->helperText('Opsional, harga sebelum diskon'),
                Textarea::make('description')->label('Deskripsi')->rows(2)->columnSpanFull(),
                Toggle::make('is_active')->label('Aktif')->default(true),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2),
            Section::make('Pengiriman')->schema([
                Radio::make('delivery_type')
                    ->label('Metode Pengiriman')
                    ->options([
                        ProductVariant::DELIVERY_AUTO => 'Otomatis — akun diambil dari Stok Digital dan langsung dikirim setelah lunas',
                        ProductVariant::DELIVERY_MANUAL => 'Manual — admin mengirim sendiri dari halaman Pesanan',
                    ])
                    ->default(ProductVariant::DELIVERY_AUTO)
                    ->required()
                    ->live()
                    ->columnSpanFull(),
                Placeholder::make('auto_stock')
                    ->label('Stok Tersedia')
                    ->content(fn (?ProductVariant $record) => $record
                        ? $record->available_stock_count.' akun di Stok Digital'
                        : 'Simpan varian, lalu isi akun di menu Stok Digital')
                    ->visible(fn (Get $get) => $get('delivery_type') === ProductVariant::DELIVERY_AUTO),
                TextInput::make('stock_quantity')
                    ->label('Jumlah Stok')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Kosongkan bila selalu tersedia. Berkurang otomatis setiap pesanan lunas.')
                    ->visible(fn (Get $get) => $get('delivery_type') === ProductVariant::DELIVERY_MANUAL),
            ])->columns(2),
            Section::make('Pengaturan Lanjutan')
                ->description('Opsional. Nilai bawaan sudah cukup untuk sebagian besar produk.')
                ->collapsed()
                ->visible(fn (Get $get) => $get('delivery_type') === ProductVariant::DELIVERY_AUTO)
                ->schema([
                    TextInput::make('max_downloads')->label('Maks. Download File')->numeric()->minValue(1)->default(5),
                    TextInput::make('download_expiry_hours')->label('Masa Berlaku Download (Jam)')->numeric()->minValue(1)->default(48),
                    Textarea::make('delivery_template')
                        ->label('Template Isi Pengiriman')
                        ->rows(6)
                        ->helperText('Kosongkan untuk template standar. Variabel: {order_number}, {customer_name}, {product_name}, {variant_name}, {duration}, serta kolom data stok seperti {username} dan {password}.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->withAvailableStockCount())
            ->columns([
                TextColumn::make('name')->label('Varian')->searchable(),
                TextColumn::make('price')->label('Harga')->money('IDR', locale: 'id')->sortable(),
                TextColumn::make('delivery_type')->label('Pengiriman')->badge()
                    ->formatStateUsing(fn (string $state) => $state === ProductVariant::DELIVERY_AUTO ? 'Otomatis' : 'Manual')
                    ->color(fn (string $state) => $state === ProductVariant::DELIVERY_AUTO ? 'info' : 'warning'),
                TextColumn::make('stock_status')->label('Stok')->badge()
                    ->color(fn (string $state) => match (true) {
                        $state === 'Habis' => 'danger',
                        str_starts_with($state, 'Tersisa') => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('is_active')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Varian'),
            ])
            ->actions([
                Tables\Actions\Action::make('stock')
                    ->label('Stok')
                    ->icon('heroicon-o-key')
                    ->visible(fn (ProductVariant $record) => $record->isAutoDelivery())
                    ->url(fn (ProductVariant $record) => DigitalStockResource::getUrl('index', ['tableFilters' => ['product_variant_id' => ['value' => $record->id]]])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
