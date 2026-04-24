<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\Device;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Rekam Data Perangkat'),
        ];
    }

    public function getTabs(): array
    {
        $komputer    = ['laptop', 'desktop', 'all-in-one', 'workstation'];
        $printer     = ['printer', 'scanner'];
        $jaringan    = ['router', 'switch', 'access-point'];
        $lainnya     = ['other'];

        return [
            'all' => Tab::make('Semua')
                ->badge(Device::count()),

            'komputer' => Tab::make('Komputer')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $komputer))
                ->badge(Device::whereIn('type', $komputer)->count())
                ->badgeColor('info'),

            'printer_scanner' => Tab::make('Printer / Scanner')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $printer))
                ->badge(Device::whereIn('type', $printer)->count())
                ->badgeColor('warning'),

            'jaringan' => Tab::make('Perangkat Jaringan')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $jaringan))
                ->badge(Device::whereIn('type', $jaringan)->count())
                ->badgeColor('success'),

            'lainnya' => Tab::make('Lainnya')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('type', $lainnya))
                ->badge(Device::whereIn('type', $lainnya)->count())
                ->badgeColor('gray'),
        ];
    }
}
