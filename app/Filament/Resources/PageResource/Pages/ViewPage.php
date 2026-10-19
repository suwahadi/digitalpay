<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPage extends ViewRecord
{
    protected static string $resource = PageResource::class;
    protected static ?string $title = 'Lihat Halaman';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Ubah'),
        ];
    }
}