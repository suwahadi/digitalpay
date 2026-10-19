<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;
    protected static ?string $title = 'Kategori';

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
