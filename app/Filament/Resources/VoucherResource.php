<?php

namespace App\Filament\Resources;

use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $modelLabel = 'Voucher Diskon';
    protected static ?string $pluralModelLabel = 'Voucher Diskon';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Voucher')->schema([
                        TextInput::make('code')
                            ->label('Kode Voucher')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('PROMO2024')
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Nama Voucher')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Diskon Bulan November')
                            ->columnSpan(2),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi voucher...')
                            ->columnSpanFull(),
                    ])->columns(3),

                    Section::make('Pengaturan Diskon')->schema([
                        Select::make('discount_type')
                            ->label('Tipe Diskon')
                            ->required()
                            ->options([
                                'fixed' => 'Nominal Tetap (Rp)',
                                'percentage' => 'Persentase (%)',
                            ])
                            ->default('fixed')
                            ->reactive()
                            ->columnSpan(1),
                        TextInput::make('discount_value')
                            ->label('Nilai Diskon')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn ($get) => $get('discount_type') === 'percentage' ? '%' : 'Rp')
                            ->placeholder('10000 atau 10')
                            ->helperText(fn ($get) => $get('discount_type') === 'percentage' 
                                ? 'Masukkan persentase disko'
                                : 'Masukkan nominal diskon')
                            ->columnSpan(1),
                        TextInput::make('max_discount')
                            ->label('Maks. Diskon (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('Rp')
                            ->placeholder('50000')
                            ->helperText('Hanya untuk diskon persentase. Kosongkan jika tidak ada batasan')
                            ->visible(fn ($get) => $get('discount_type') === 'percentage')
                            ->columnSpan(1),
                        TextInput::make('min_total')
                            ->label('Min. Pembelian (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('Rp')
                            ->placeholder('100000')
                            ->helperText('Minimal total belanja')
                            ->columnSpan(1),
                    ])->columns(3),

                    Section::make('Periode & Penggunaan')->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Mulai Berlaku')
                            ->placeholder('Pilih tanggal mulai')
                            ->helperText('Kosongkan jika berlaku segera')
                            ->columnSpan(1),
                        DateTimePicker::make('expires_at')
                            ->label('Berakhir')
                            ->placeholder('Pilih tanggal berakhir')
                            ->helperText('Kosongkan jika tidak ada batas waktu')
                            ->columnSpan(1),
                        TextInput::make('usage_limit')
                            ->label('Batas Penggunaan')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('100')
                            ->helperText('Kosongkan jika unlimited')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Aktifkan/nonaktifkan voucher')
                            ->columnSpan(1),
                    ])->columns(3),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $state === 'fixed' ? 'Nominal' : 'Persentase')
                    ->badge()
                    ->color(fn ($state) => $state === 'fixed' ? 'success' : 'warning'),
                TextColumn::make('discount_value')
                    ->label('Nilai Diskon')
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->discount_type === 'percentage' 
                            ? number_format($state, 0) . '%' 
                            : 'Rp ' . number_format($state, 0, ',', '.')
                    )
                    ->sortable(),
                TextColumn::make('min_total')
                    ->label('Min. Belanja')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('usage_stats')
                    ->label('Penggunaan')
                    ->formatStateUsing(fn ($record) => 
                        $record->used_count . ' / ' . ($record->usage_limit ?: '∞')
                    )
                    ->badge()
                    ->color(fn ($record) => 
                        $record->usage_limit && $record->used_count >= $record->usage_limit 
                            ? 'danger' 
                            : 'success'
                    )->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y') : 'Unlimited')
                    ->badge()
                    ->color(fn ($state) => 
                        !$state ? 'gray' : ($state->isPast() ? 'danger' : 'success')
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
                Tables\Filters\SelectFilter::make('discount_type')
                    ->label('Tipe Diskon')
                    ->options([
                        'fixed' => 'Nominal',
                        'percentage' => 'Persentase',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => VoucherResource\Pages\ListVouchers::route('/'),
        ];
    }
}
