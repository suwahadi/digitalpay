<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingResource::class;
    protected static ?string $title = 'Pengaturan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengaturan')
                ->createAnother(false),
        ];
    }
}