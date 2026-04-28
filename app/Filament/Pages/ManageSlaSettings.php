<?php

namespace App\Filament\Pages;

use App\Settings\SlaSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ManageSlaSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'SLA Helpdesk';

    protected static ?int $navigationSort = 3;

    protected static string $settings = SlaSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('critical_hours')
                    ->label('SLA Prioritas Kritis')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('jam')
                    ->required()
                    ->helperText('Batas waktu penyelesaian tiket prioritas kritis'),

                TextInput::make('high_hours')
                    ->label('SLA Prioritas Tinggi')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('jam')
                    ->required()
                    ->helperText('Batas waktu penyelesaian tiket prioritas tinggi'),

                TextInput::make('medium_hours')
                    ->label('SLA Prioritas Sedang')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('jam')
                    ->required()
                    ->helperText('Batas waktu penyelesaian tiket prioritas sedang'),

                TextInput::make('low_hours')
                    ->label('SLA Prioritas Rendah')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('jam')
                    ->required()
                    ->helperText('Batas waktu penyelesaian tiket prioritas rendah'),
            ]);
    }
}
