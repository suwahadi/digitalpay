<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Transaksi', $this->getMonthlyOrderCount())
                ->description('Bulan ini')
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Transaksi Sukses', $this->getMonthlySuccessTotal())
                ->description('Bulan ini')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Transaksi Bulan ini', $this->getMonthlyTotal())
                ->description('Total keseluruhan')
                ->color('info')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Member Terdaftar', $this->getTotalMembers())
                ->description('Total member')
                ->color('warning')
                ->icon('heroicon-o-user-group'),
        ];
    }

    protected function getMonthlyOrderCount(): string
    {
        $count = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        return $count . 'x';
    }

    protected function getMonthlySuccessTotal(): string
    {
        $total = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('payment_status', 'paid')
            ->where('status', 'paid')
            ->sum('total_amount');
        
        return 'Rp ' . number_format($total, 0, ',', '.');
    }

    protected function getMonthlyTotal(): string
    {
        $total = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');
        
        return 'Rp ' . number_format($total, 0, ',', '.');
    }

    protected function getTotalMembers(): string
    {
        $count = User::where('role', 'member')->count();
        
        return (string) $count;
    }
}
