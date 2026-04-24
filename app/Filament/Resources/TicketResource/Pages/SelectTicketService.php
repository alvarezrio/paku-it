<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\Page;

class SelectTicketService extends Page
{
    protected static string $resource = TicketResource::class;

    protected static string $view = 'filament.resources.ticket-resource.pages.select-ticket-service';

    protected static ?string $title = 'Rekam Tiket Baru';

    public function getSubheading(): ?string
    {
        return 'Pilih jenis layanan yang sesuai dengan kebutuhan Anda. Tim IT Helpdesk siap membantu.';
    }

    public function getServices(): array
    {
        return [
            [
                'key'         => 'incident_management',
                'label'       => 'Incident Management',
                'description' => 'Menangani gangguan atau masalah teknis yang terjadi pada sistem, perangkat, atau aplikasi agar kembali normal secepat mungkin.',
                'icon'        => 'heroicon-o-exclamation-triangle',
                'color'       => 'danger',
            ],
            [
                'key'         => 'service_request',
                'label'       => 'Service Request',
                'description' => 'Melayani permintaan pengguna terkait kebutuhan IT, seperti instalasi software, pembuatan akun, atau reset password.',
                'icon'        => 'heroicon-o-clipboard-document-list',
                'color'       => 'info',
            ],
            [
                'key'         => 'user_support',
                'label'       => 'User Support',
                'description' => 'Memberikan bantuan dan panduan kepada pengguna dalam menggunakan perangkat atau aplikasi IT.',
                'icon'        => 'heroicon-o-user-circle',
                'color'       => 'success',
            ],
            [
                'key'         => 'access_management',
                'label'       => 'Access Management',
                'description' => 'Mengatur dan mengelola hak akses pengguna terhadap sistem atau data sesuai dengan peran masing-masing.',
                'icon'        => 'heroicon-o-key',
                'color'       => 'warning',
            ],
            [
                'key'         => 'asset_management',
                'label'       => 'Asset Management',
                'description' => 'Melakukan pendataan, pemantauan, dan pengelolaan seluruh aset IT seperti komputer, laptop, dan perangkat lainnya.',
                'icon'        => 'heroicon-o-computer-desktop',
                'color'       => 'primary',
            ],
            [
                'key'         => 'change_management',
                'label'       => 'Change Management',
                'description' => 'Mengelola perubahan pada sistem atau infrastruktur IT agar berjalan terencana dan tidak menimbulkan gangguan.',
                'icon'        => 'heroicon-o-arrow-path',
                'color'       => 'warning',
            ],
            [
                'key'         => 'network_support',
                'label'       => 'Network Support',
                'description' => 'Menangani permasalahan jaringan seperti koneksi internet, WiFi, maupun konfigurasi jaringan internal.',
                'icon'        => 'heroicon-o-wifi',
                'color'       => 'info',
            ],
            [
                'key'         => 'security_support',
                'label'       => 'Security Support',
                'description' => 'Menjaga keamanan sistem dan data dari ancaman seperti virus, malware, atau akses tidak sah.',
                'icon'        => 'heroicon-o-shield-check',
                'color'       => 'danger',
            ],
            [
                'key'         => 'documentation_kb',
                'label'       => 'Documentation & Knowledge Base',
                'description' => 'Menyediakan dokumentasi, panduan, dan solusi atas masalah umum sebagai referensi bagi pengguna.',
                'icon'        => 'heroicon-o-book-open',
                'color'       => 'gray',
            ],
        ];
    }

    public function getCreateUrl(string $category): string
    {
        return TicketResource::getUrl('create', ['category' => $category]);
    }
}
