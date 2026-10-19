<?php

namespace App\Filament\Resources;

use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $modelLabel = 'Metode Pembayaran';

    protected static ?string $pluralModelLabel = 'Metode Pembayaran';

    private const TYPES = [
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
        'virtual_account' => 'Virtual Account',
        'bank_transfer' => 'Transfer Bank',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Kode')
                    ->helperText('Dipakai sistem untuk mengenali metode (mis. qris, gopay, bca_va). Hindari mengubah pada metode yang sudah dipakai.')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Jenis')
                    ->options(self::TYPES)
                    ->required(),
                TextInput::make('provider')
                    ->label('Provider')
                    ->helperText('Gateway/penyedia, mis. midtrans.')
                    ->required()
                    ->maxLength(100),
                FileUpload::make('icon')
                    ->label('Ikon')
                    ->image()
                    ->directory('payment')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Kosongkan untuk mempertahankan ikon saat ini. Gambar PNG/JPG akan otomatis dikonversi ke WebP; SVG belum didukung.'),
                Select::make('fee_type')
                    ->label('Jenis Biaya')
                    ->placeholder('Tanpa biaya')
                    ->options([
                        'fixed' => 'Nominal tetap (Rp)',
                        'percentage' => 'Persentase (%)',
                    ])
                    ->live(),
                TextInput::make('fee_amount')
                    ->label('Besar Biaya')
                    ->helperText(fn (Forms\Get $get) => $get('fee_type') === 'percentage' ? 'Dalam persen, mis. 1.5 untuk 1,5%.' : 'Dalam Rupiah.')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('min_amount')
                    ->label('Minimum Transaksi (Rp)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                TextInput::make('max_amount')
                    ->label('Maksimum Transaksi (Rp)')
                    ->helperText('Kosongkan bila tanpa batas.')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('sort_order')
                    ->label('Urutan')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon')
                    ->label('Ikon')
                    ->getStateUsing(fn (PaymentMethod $record) => $record->icon_url)
                    ->height(28),
                TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn (PaymentMethod $record) => $record->code)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state),
                TextColumn::make('fee_amount')
                    ->label('Biaya')
                    ->formatStateUsing(function (PaymentMethod $record) {
                        if (!$record->fee_type) {
                            return 'Gratis';
                        }

                        return $record->fee_type === 'percentage'
                            ? rtrim(rtrim(number_format((float) $record->fee_amount, 2), '0'), '.') . '%'
                            : 'Rp ' . number_format((float) $record->fee_amount, 0, ',', '.');
                    }),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(self::TYPES),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->searchPlaceholder('Cari metode');
    }

    public static function getPages(): array
    {
        return [
            'index' => PaymentMethodResource\Pages\ManagePaymentMethods::route('/'),
        ];
    }
}
