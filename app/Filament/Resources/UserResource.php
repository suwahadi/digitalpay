<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Hash;
use Filament\Tables;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationGroup = 'Pengguna';
    
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $modelLabel = 'Pengguna';
    
    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(3)->schema([
                Section::make('Informasi Akun')->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->tel()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20),
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create'),
                    Select::make('role')
                        ->label('Peran')
                        ->options([
                            'admin' => 'Admin',
                            'member' => 'Member',
                        ])
                        ->default('member')
                        ->required(),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'suspended' => 'Ditangguhkan',
                        ])
                        ->default('active')
                        ->required(),
                ])->columns(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=667eea&color=fff')
                    ->size(50),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin' => 'Admin',
                        'member' => 'Member',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'success',
                        'member' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        'suspended' => 'Ditangguhkan',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
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
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'member' => 'Member',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        'suspended' => 'Ditangguhkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'asc')
            ->searchPlaceholder('Cari');
    }

    public static function getPages(): array
    {
        return [
            'index' => UserResource\Pages\ListUsers::route('/'),
        ];
    }
}
