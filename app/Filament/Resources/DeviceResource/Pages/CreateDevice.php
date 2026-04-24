<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\DeviceAttribute;
use Filament\Resources\Pages\CreateRecord;

class CreateDevice extends CreateRecord
{
    protected static string $resource = DeviceResource::class;
    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Rekam Data Perangkat';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove dynamic_attributes from main data as they'll be saved separately
        unset($data['dynamic_attributes']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->saveDynamicAttributes();
    }

    protected function saveDynamicAttributes(): void
    {
        $dynamicData = $this->data['dynamic_attributes'] ?? [];

        foreach ($dynamicData as $slug => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $attribute = DeviceAttribute::where('slug', $slug)->first();
            if ($attribute) {
                $this->record->attributeValues()->updateOrCreate(
                    ['device_attribute_id' => $attribute->id],
                    ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
                );
            }
        }
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
