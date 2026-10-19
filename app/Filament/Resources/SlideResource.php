<?php

namespace App\Filament\Resources;

use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;
    protected static ?string $navigationGroup = 'Konten';
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $modelLabel = 'Slide';
    protected static ?string $pluralModelLabel = 'Slide';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                    ->label('Gambar')
                    ->image()
                    ->directory('slides')
                    ->required(),
                TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                TextInput::make('link')
                    ->label('Link'),
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
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->square(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('link')
                    ->label('Link')
                    ->limit(30)
                    ->copyable(),
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
            ->searchPlaceholder('Cari')
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => SlideResource\Pages\ManageSlides::route('/'),
        ];
    }
}