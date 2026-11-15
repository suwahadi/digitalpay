<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;
    protected static ?string $title = 'Lihat Order';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('deliver_manual')
                ->label('Kirim Manual')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->visible(fn () => OrderResource::canDeliverManually($this->record))
                ->form(OrderResource::manualDeliveryForm())
                ->modalHeading('Kirim Manual - '.$this->record->order_number)
                ->modalSubmitActionLabel('Kirim ke Pembeli')
                ->action(function (array $data) {
                    OrderResource::deliverManually($this->record, $data);
                    $this->record->refresh();
                }),
            Actions\Action::make('mark_paid')
                ->label('Tandai Lunas')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => OrderResource::canMarkPaid($this->record))
                ->requiresConfirmation()
                ->modalHeading('Tandai pesanan lunas?')
                ->modalDescription(fn () => OrderResource::markPaidDescription($this->record))
                ->modalSubmitActionLabel('Ya, tandai lunas')
                ->action(function () {
                    OrderResource::markPaid($this->record);
                    $this->record->refresh();
                }),
            Actions\EditAction::make()->label('Ubah'),
        ];
    }
}