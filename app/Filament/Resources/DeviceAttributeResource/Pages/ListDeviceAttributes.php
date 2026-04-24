<?php

namespace App\Filament\Resources\DeviceAttributeResource\Pages;

use App\Filament\Resources\DeviceAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeviceAttributes extends ListRecords
{
    protected static string $resource = DeviceAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Rekam Data Atribut'),
        ];
    }
}
