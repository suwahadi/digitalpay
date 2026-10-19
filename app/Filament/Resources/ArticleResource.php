<?php

namespace App\Filament\Resources;

use App\Models\Article;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationGroup = 'Konten';
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $modelLabel = 'Artikel';
    protected static ?string $pluralModelLabel = 'Artikel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Informasi Artikel')->schema([
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
                        Select::make('author_id')
                            ->label('Penulis')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'berita' => 'Berita',
                                'tutorial' => 'Tutorial',
                                'tips' => 'Tips',
                                'review' => 'Review',
                                'panduan' => 'Panduan',
                                'umum' => 'Umum',
                            ])
                            ->required(),
                        Toggle::make('is_published')->label('Dipublikasi')->default(true),
                        Toggle::make('is_featured')->label('Artikel Unggulan'),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->default(now()),
                    ])->columns(2),
                    Section::make('Media')->schema([
                        FileUpload::make('featured_image')
                            ->label('Gambar Unggulan')
                            ->image()
                            ->directory('articles'),
                    ])->columns(1),
                    Section::make('Konten')->schema([
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->columnSpanFull(),
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
                    Section::make('Tags')->schema([
                        TagsInput::make('tags')
                            ->label('Tags')
                            ->columnSpanFull(),
                    ])->columns(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')->label('Gambar')->square(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'berita' => 'Berita',
                        'tutorial' => 'Tutorial',
                        'tips' => 'Tips',
                        'review' => 'Review',
                        'panduan' => 'Panduan',
                        'umum' => 'Umum',
                        default => ucfirst($state)
                    })
                    ->color(fn ($state) => match($state) {
                        'berita' => 'info',
                        'tutorial' => 'success',
                        'tips' => 'warning',
                        'review' => 'danger',
                        'panduan' => 'primary',
                        'umum' => 'gray',
                        default => 'gray'
                    }),
                TextColumn::make('is_published')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Dipublikasi' : 'Draft')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                TextColumn::make('is_featured')
                    ->label('Unggulan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->color(fn ($state) => $state ? 'info' : 'gray'),
                TextColumn::make('published_at')
                    ->label('Tgl Publikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
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
                        'berita' => 'Berita',
                        'tutorial' => 'Tutorial',
                        'tips' => 'Tips',
                        'review' => 'Review',
                        'panduan' => 'Panduan',
                        'umum' => 'Umum',
                    ]),
                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->trueLabel('Dipublikasi')
                    ->falseLabel('Draft'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Artikel Unggulan')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => ArticleResource\Pages\ListArticles::route('/'),
            'create' => ArticleResource\Pages\CreateArticle::route('/create'),
            'view' => ArticleResource\Pages\ViewArticle::route('/{record}'),
            'edit' => ArticleResource\Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}