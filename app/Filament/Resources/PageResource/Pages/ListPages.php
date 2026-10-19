<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'Halaman';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah')->createAnother(false),
        ];
    }
}