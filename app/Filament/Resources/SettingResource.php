<?php

namespace App\Filament\Resources;

use App\Models\Setting;
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

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationGroup = 'Sistem';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $modelLabel = 'Pengaturan';
    protected static ?string $pluralModelLabel = 'Pengaturan';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('key_name')
                    ->label('Key')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('value')
                    ->label('Value')
                    ->required()
                    ->rows(3),
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'string' => 'String',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                    ])
                    ->default('string')
                    ->required(),
                Select::make('group_name')
                    ->label('Group')
                    ->options([
                        'general' => 'General',
                        'site' => 'Site',
                        'payment' => 'Payment',
                        'email' => 'Email',
                        'social' => 'Social',
                        'seo' => 'SEO',
                    ])
                    ->default('general')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2),
                Toggle::make('is_public')
                    ->label('Public')
                    ->default(false),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key_name')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->value),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'string' => 'gray',
                        'number' => 'info',
                        'boolean' => 'success',
                        'json' => 'warning',
                        default => 'gray'
                    }),
                TextColumn::make('group_name')
                    ->label('Group')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'general' => 'gray',
                        'site' => 'info',
                        'payment' => 'success',
                        'email' => 'warning',
                        'social' => 'primary',
                        'seo' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('is_public')
                    ->label('Public')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'string' => 'String',
                        'number' => 'Number',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                    ]),
                Tables\Filters\SelectFilter::make('group_name')
                    ->label('Group')
                    ->options([
                        'general' => 'General',
                        'site' => 'Site',
                        'payment' => 'Payment',
                        'email' => 'Email',
                        'social' => 'Social',
                        'seo' => 'SEO',
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public')
                    ->trueLabel('Ya')
                    ->falseLabel('Tidak'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('group_name', 'asc')
            ->searchPlaceholder('Cari key atau value')
            ->reorderable('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => SettingResource\Pages\ManageSettings::route('/'),
        ];
    }
}