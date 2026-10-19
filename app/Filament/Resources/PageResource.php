<?php

namespace App\Filament\Resources;

use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationGroup = 'Konten';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Halaman';
    protected static ?string $pluralModelLabel = 'Halaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Halaman')->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                if (!filled($get('slug'))) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Toggle::make('is_published')
                            ->label('Dipublikasi')
                            ->default(true),
                    ])->columns(2),
                    Section::make('Konten')->schema([
                        RichEditor::make('content')
                            ->label('Konten')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ]),
                    ])->columns(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug berhasil disalin')
                    ->limit(30),
                TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Dipublikasi' : 'Draft')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->trueLabel('Dipublikasi')
                    ->falseLabel('Draft'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->update(['is_published' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->color('success'),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Jadikan Draft')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_published' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->color('warning'),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => PageResource\Pages\ListPages::route('/'),
            'create' => PageResource\Pages\CreatePage::route('/create'),
            'view' => PageResource\Pages\ViewPage::route('/{record}'),
            'edit' => PageResource\Pages\EditPage::route('/{record}/edit'),
        ];
    }
}