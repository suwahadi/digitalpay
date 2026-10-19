<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    
    protected static ?string $title = 'Pengguna';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah')
                ->createAnother(false),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()->label('Ubah'),
            DeleteAction::make()->label('Hapus'),
        ];
    }
}
