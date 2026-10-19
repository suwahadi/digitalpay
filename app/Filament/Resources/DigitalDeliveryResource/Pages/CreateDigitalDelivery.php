<?php

namespace App\Filament\Resources\DigitalDeliveryResource\Pages;

use App\Filament\Resources\DigitalDeliveryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateDigitalDelivery extends CreateRecord
{
    protected static string $resource = DigitalDeliveryResource::class;
    protected static ?string $title = 'Tambah Digital Delivery';

    protected function afterCreate(): void
    {
        $delivery = $this->record;
        
        // Show notification to admin
        if ($delivery->delivery_trigger === 'manual' && !empty($delivery->delivery_content)) {
            Notification::make()
                ->title('Manual Delivery Dibuat')
                ->body('Email notifikasi akan dikirim ke customer dalam beberapa saat.')
                ->success()
                ->send();
        } elseif ($delivery->delivery_trigger === 'manual' && empty($delivery->delivery_content)) {
            Notification::make()
                ->title('Delivery Dibuat Tanpa Konten')
                ->body('Email notifikasi tidak akan dikirim karena konten delivery kosong. Silakan edit dan isi konten delivery.')
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}