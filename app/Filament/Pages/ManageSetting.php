<?php

namespace App\Filament\Pages;

use App\Settings\KaidoSetting;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageSetting extends SettingsPage
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = KaidoSetting::class;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Situs')->columns(1)->schema([
                    TextInput::make('site_name')
                        ->label('Nama Situs')
                        ->required(),
                    Toggle::make('site_active')
                        ->label('Situs Aktif'),
                    Toggle::make('registration_enabled')
                        ->label('Pendaftaran Diaktifkan'),
                    Toggle::make('password_reset_enabled')
                        ->label('Reset Password Diaktifkan'),
                    Toggle::make('sso_enabled')
                        ->label('SSO Diaktifkan'),
                ]),
            ]);
    }
}
