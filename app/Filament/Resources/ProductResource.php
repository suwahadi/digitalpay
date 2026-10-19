<?php

namespace App\Filament\Resources;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Katalog';
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Produk')->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if (!filled($get('slug'))) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique(ignoreRecord: true),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_active')->label('Aktif')->default(true),
                        Toggle::make('is_featured')->label('Sorotan'),
                        Toggle::make('is_popular')->label('Populer'),
                        Toggle::make('is_digital')->label('Produk Digital')->default(false),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                    ])->columns(3),
                    Section::make('Media')->schema([
                        FileUpload::make('image')->label('Gambar Utama')->image(),
                    ])->columns(1),
                    Section::make('Deskripsi')->schema([
                        RichEditor::make('description')->label('Deskripsi')->columnSpanFull(),
                        TextInput::make('short_description')->label('Deskripsi Singkat')->columnSpanFull(),
                    ])->columns(1),
                    Section::make('Fitur')->schema([
                        KeyValue::make('features')->label('Fitur')->columnSpanFull(),
                    ])->columns(1),
                    Section::make('Instruksi Digital')->schema([
                        RichEditor::make('digital_instructions')->label('Instruksi')->columnSpanFull(),
                    ])->columns(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Gambar')->square(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Kategori')->sortable(),
                TextColumn::make('is_active')->label('Aktif')->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                TextColumn::make('is_digital')->label('Digital')->badge()
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
                Tables\Filters\SelectFilter::make('category_id')->label('Kategori')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status')
                    ->trueLabel('Aktif')->falseLabel('Nonaktif'),
                Tables\Filters\TernaryFilter::make('is_digital')->label('Produk Digital')
                    ->trueLabel('Ya')->falseLabel('Tidak'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->searchPlaceholder('Cari')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ProductResource\Pages\ListProducts::route('/'),
            'create' => ProductResource\Pages\CreateProduct::route('/create'),
            'edit' => ProductResource\Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
