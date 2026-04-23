<?php

namespace App\Filament\Pages;

use App\Settings\ModuleSettings;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ManageModuleSettings extends SettingsPage
{
    use HasPageShield;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-vertical';

    protected static ?string $title = 'Manajemen Modul';

    protected static ?int $navigationSort = 2;

    protected static string $settings = ModuleSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('enable_vehicle_booking')
                    ->label('Aktifkan Peminjaman KDO')
                    ->helperText('Aktifkan atau nonaktifkan modul Peminjaman Kendaraan Dinas.'),
                Toggle::make('enable_helpdesk_tickets')
                    ->label('Aktifkan Tiket Helpdesk')
                    ->helperText('Aktifkan atau nonaktifkan modul Tiket Helpdesk.'),
                Toggle::make('enable_inventory')
                    ->label('Aktifkan Inventaris IT')
                    ->helperText('Aktifkan atau nonaktifkan modul Inventaris Perangkat IT.'),
                Toggle::make('enable_blog')
                    ->label('Aktifkan Blog/Artikel')
                    ->helperText('Aktifkan atau nonaktifkan modul Artikel/Basis Pengetahuan.'),
                Toggle::make('enable_user_management')
                    ->label('Aktifkan Manajemen Pengguna')
                    ->helperText('Aktifkan atau nonaktifkan modul Manajemen Pengguna dan Peran.'),
            ]);
    }
}
