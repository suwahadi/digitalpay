<?php

namespace App\Filament\Resources;

use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static ?string $navigationGroup = 'Konten';
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $modelLabel = 'FAQ';
    protected static ?string $pluralModelLabel = 'FAQ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('question')
                    ->label('Pertanyaan')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('answer')
                    ->label('Jawaban')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'umum' => 'Umum',
                        'pembayaran' => 'Pembayaran',
                        'produk' => 'Produk',
                        'akun' => 'Akun',
                        'layanan' => 'Layanan',
                        'teknis' => 'Teknis',
                    ])
                    ->searchable()
                    ->required(),
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
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'umum' => 'Umum',
                        'pembayaran' => 'Pembayaran',
                        'produk' => 'Produk',
                        'akun' => 'Akun',
                        'layanan' => 'Layanan',
                        'teknis' => 'Teknis',
                        default => ucfirst($state)
                    })
                    ->color(fn ($state) => match($state) {
                        'umum' => 'gray',
                        'pembayaran' => 'success',
                        'produk' => 'info',
                        'akun' => 'warning',
                        'layanan' => 'primary',
                        'teknis' => 'danger',
                        default => 'gray'
                    })
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
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
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'umum' => 'Umum',
                        'pembayaran' => 'Pembayaran',
                        'produk' => 'Produk',
                        'akun' => 'Akun',
                        'layanan' => 'Layanan',
                        'teknis' => 'Teknis',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
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
            ->defaultSort('sort_order', 'asc')
            ->searchPlaceholder('Cari')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => FaqResource\Pages\ManageFaqs::route('/'),
        ];
    }
}