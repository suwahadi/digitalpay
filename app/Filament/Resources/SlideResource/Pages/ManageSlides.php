<?php

namespace App\Filament\Resources\SlideResource\Pages;

use App\Filament\Resources\SlideResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSlides extends ManageRecords
{
    protected static string $resource = SlideResource::class;
    protected static ?string $title = 'Slide';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Slide')
                ->createAnother(false),
        ];
    }
}