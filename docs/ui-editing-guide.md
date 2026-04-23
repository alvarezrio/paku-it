# Panduan Edit UI — Employee Digital Hub

Proyek ini menggunakan **Laravel 12 + Filament 3** sebagai admin panel.
Sebagian besar UI dikontrol lewat **PHP class**, bukan file Blade secara langsung.

---

## Daftar Isi

1. [Cara Kerja UI di Filament](#cara-kerja-ui-di-filament)
2. [Peta Lengkap File UI](#peta-lengkap-file-ui)
3. [Sidebar & Navigasi Menu](#sidebar--navigasi-menu)
4. [Mengubah Label Teks di Sidebar](#mengubah-label-teks-di-sidebar)
5. [Tabel (List View)](#tabel-list-view)
6. [Form (Create / Edit)](#form-create--edit)
7. [Detail View (Infolist)](#detail-view-infolist)
8. [Dashboard & Widget](#dashboard--widget)
9. [Halaman Custom](#halaman-custom)
10. [Tampilan Login](#tampilan-login)
11. [Tema & Warna](#tema--warna)
12. [Komponen Blade](#komponen-blade)
13. [Komponen Livewire](#komponen-livewire)
14. [Manajemen Aset (CSS & JavaScript)](#manajemen-aset-css--javascript)
15. [Lokalialisasi Teks](#lokalialisasi-teks)
16. [Referensi Cepat](#referensi-cepat)

---

## Cara Kerja UI di Filament

```
Ingin ubah...          Lihat ke...
─────────────────────────────────────────────────────────
Menu sidebar         → PHP class (Resource / Page)
Kolom tabel          → PHP class (method table())
Field form           → PHP class (method form())
Tampilan HTML        → Blade view (.blade.php)
Teks bawaan Filament → lang/vendor/filament-panels/id/
```

---

## Peta Lengkap File UI

```
app/Filament/
├── Pages/                              # Halaman custom
│   ├── Login.php                       # Logika halaman login
│   ├── Register.php                    # Logika halaman registrasi
│   ├── ManageSetting.php               # Halaman pengaturan aplikasi
│   ├── VehicleCalendar.php             # Halaman kalender kendaraan
│   └── TicketReport.php                # Halaman laporan tiket
│
├── Resources/                          # Modul CRUD (otomatis muncul di sidebar)
│   ├── UserResource.php                # Manajemen pegawai
│   ├── RoleResource.php                # Manajemen role/hak akses
│   ├── DeviceResource.php              # Inventaris perangkat IT
│   ├── DeviceAttributeResource.php     # Atribut/spesifikasi perangkat
│   ├── TicketResource.php              # Tiket layanan IT
│   ├── VehicleResource.php             # Master kendaraan dinas
│   ├── VehicleBookingResource.php      # Peminjaman kendaraan
│   ├── ArticleResource.php             # Artikel knowledge base
│   └── CategoryResource.php            # Kategori artikel
│
└── Widgets/                            # Widget statistik di dashboard
    ├── DeviceStatsWidget.php
    ├── TicketStatsWidget.php
    ├── VehicleBookingStats.php
    ├── VehicleAvailabilityCalendar.php
    ├── RecentTicketsWidget.php
    └── MyActiveBookings.php

app/Providers/Filament/
└── AdminPanelProvider.php              # Konfigurasi utama panel (warna, plugin, dll)

resources/views/
├── filament/
│   ├── pages/
│   │   ├── login.blade.php             # Tampilan HTML halaman login
│   │   ├── vehicle-calendar.blade.php  # Tampilan HTML kalender kendaraan
│   │   └── ticket-report.blade.php     # Tampilan HTML laporan tiket
│   ├── widgets/
│   │   ├── device-stats-widget.blade.php
│   │   ├── ticket-stats-widget.blade.php
│   │   └── vehicle-availability-calendar.blade.php
│   ├── resources/
│   │   └── ticket-resource/
│   │       ├── partials/
│   │       │   └── attachments.blade.php
│   │       └── widgets/
│   │           └── ticket-chat-widget.blade.php
│   └── components/
│       └── logout-button.blade.php     # Tombol logout di user menu
├── livewire/
│   └── ticket-chat.blade.php           # Komponen chat tiket real-time
├── vendor/
│   ├── filament-breezy/                # Override tampilan profil & 2FA
│   └── filament-socialite/             # Override tombol login Google
├── reports/
│   └── ticket-pdf.blade.php            # Template cetak PDF tiket
├── welcome.blade.php                   # Landing page publik
└── article.blade.php                   # Halaman artikel publik

lang/vendor/filament-panels/
└── id/
    └── pages/
        └── dashboard.php               # Override teks "Dashboard" bawaan Filament
```

---

## Sidebar & Navigasi Menu

### Cara kerja

Sidebar **otomatis terisi** dari dua sumber:
- Semua file di `app/Filament/Resources/`
- Semua file di `app/Filament/Pages/`

Tidak perlu mendaftarkan menu secara manual.

### Mengelompokkan menu

```php
// app/Filament/Resources/DeviceResource.php
protected static ?string $navigationGroup = 'Inventaris';
```

### Mengubah urutan menu

Angka lebih kecil = posisi lebih atas di sidebar.

```php
protected static ?int $navigationSort = 1;
```

### Mengubah ikon menu

Gunakan nama ikon dari [Heroicons](https://heroicons.com/).

```php
protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
```

### Menyembunyikan menu dari sidebar

```php
protected static bool $shouldRegisterNavigation = false;
```

---

## Mengubah Label Teks di Sidebar

### Resource (DeviceResource, TicketResource, dll.)

Ubah langsung property di class Resource:

```php
// app/Filament/Resources/DeviceResource.php
protected static ?string $navigationLabel  = 'Perangkat';        // teks di sidebar
protected static ?string $modelLabel       = 'Perangkat';        // label singular
protected static ?string $pluralModelLabel = 'Daftar Perangkat'; // judul halaman list
```

| Property | Efek |
|---|---|
| `$navigationLabel` | Teks yang muncul di sidebar |
| `$modelLabel` | Label singular (misal: tombol "Tambah Perangkat") |
| `$pluralModelLabel` | Label plural (misal: judul halaman list) |

### Halaman Dashboard ("Dashboard" / "Dasbor")

Teks ini berasal dari translation bawaan Filament.
> Jangan edit file di `vendor/` — akan tertimpa saat `composer update`.

File override sudah tersedia di:
**`lang/vendor/filament-panels/id/pages/dashboard.php`**

```php
return [
    'title' => 'Dashboard', // <- ubah teks di sini
];
```

### Halaman Custom (VehicleCalendar, TicketReport, dll.)

```php
// app/Filament/Pages/VehicleCalendar.php
protected static ?string $navigationLabel = 'Kalender Kendaraan';
protected static ?string $title           = 'Kalender Peminjaman Kendaraan';
```

---

## Lokalialisasi Teks

Laravel menyediakan sistem lokalialisasi yang kuat untuk mengelola teks dalam berbagai bahasa.

### Lokasi File Bahasa

File bahasa disimpan di direktori `lang/`.
*   `lang/id/`: Berisi file terjemahan untuk Bahasa Indonesia.
*   `lang/en/`: Berisi file terjemahan untuk Bahasa Inggris (jika ada).

Di dalam direktori bahasa, Anda bisa memiliki file PHP (misalnya `lang/id/app.php`) yang mengembalikan array asosiatif:

```php
// lang/id/app.php
return [
    'welcome' => 'Selamat Datang di Aplikasi Kami',
    'hello'   => 'Halo, :name', // Dengan placeholder
];
```

Selain itu, Filament juga memiliki direktori bahasanya sendiri di `lang/vendor/filament-panels/id/` untuk *override* teks bawaan Filament.

### Menggunakan Teks Terjemahan

Untuk mengambil teks terjemahan di Blade view atau file PHP, gunakan helper `__` (double underscore):

```blade
<h1>{{ __('app.welcome') }}</h1>

<p>{{ __('app.hello', ['name' => $userName]) }}</p>
```

Untuk teks di dalam class PHP (misalnya di Filament Resource atau Page), Anda bisa menggunakan:

```php
use Illuminate\Support\Facades\Lang;

// ...
'label' => Lang::get('app.welcome'),
// atau
'label' => __('app.welcome'),
```

### Menambahkan Terjemahan Baru

1.  **Buat File Bahasa**: Jika Anda ingin mengelola teks terjemahan dalam grup, buat file PHP baru di `lang/id/` (misalnya `lang/id/messages.php`).
2.  **Tambahkan Key-Value**: Isi file tersebut dengan *key* dan nilai terjemahannya.
3.  **Gunakan**: Panggil terjemahan Anda dengan `__('messages.nama_key_anda')`.

---

## Tabel (List View)

File: `app/Filament/Resources/NamaResource.php` — method `table()`

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Pegawai')   // label header kolom
                ->searchable()
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Status'),
        ])
        ->filters([...])    // filter dropdown
        ->actions([...]);   // tombol aksi per baris
}
```

| Bagian | Fungsi |
|---|---|
| `->columns([...])` | Kolom yang ditampilkan di tabel |
| `->label('...')` | Teks header kolom |
| `->searchable()` | Kolom bisa dicari |
| `->sortable()` | Kolom bisa diurutkan |
| `->filters([...])` | Filter tersedia di atas tabel |
| `->actions([...])` | Tombol aksi per baris (Edit, Delete, View) |
| `->bulkActions([...])` | Aksi massal (pilih banyak baris) |

---

## Form (Create / Edit)

File: `app/Filament/Resources/NamaResource.php` — method `form()`

```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nama')
                ->label('Nama Pegawai')
                ->required()
                ->placeholder('Masukkan nama...'),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'aktif'    => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ]),

            Forms\Components\Section::make('Informasi Tambahan') // grup field
                ->schema([...]),
        ]);
}
```

| Method | Fungsi |
|---|---|
| `->label('...')` | Label field |
| `->placeholder('...')` | Teks placeholder input |
| `->helperText('...')` | Teks kecil di bawah field |
| `->required()` | Wajib diisi |
| `->disabled()` | Field tidak bisa diedit |
| `->hidden()` | Sembunyikan field |

---

## Detail View (Infolist)

File: `app/Filament/Resources/NamaResource.php` — method `infolist()`

```php
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            Section::make('Informasi Perangkat')   // judul section
                ->schema([
                    TextEntry::make('nama')->label('Nama'),
                    TextEntry::make('status')->label('Status'),
                ]),
        ]);
}
```

---

## Dashboard & Widget

### Setiap widget punya dua file

| File PHP (logika) | File Blade (tampilan HTML) |
|---|---|
| `app/Filament/Widgets/DeviceStatsWidget.php` | `resources/views/filament/widgets/device-stats-widget.blade.php` |
| `app/Filament/Widgets/TicketStatsWidget.php` | `resources/views/filament/widgets/ticket-stats-widget.blade.php` |
| `app/Filament/Widgets/VehicleAvailabilityCalendar.php` | `resources/views/filament/widgets/vehicle-availability-calendar.blade.php` |

Edit file **Blade** untuk mengubah tampilan HTML widget.
Edit file **PHP** untuk mengubah data dan logika widget.

### Mengubah urutan widget di dashboard

```php
// app/Filament/Widgets/DeviceStatsWidget.php
protected static ?int $sort = 1; // angka kecil = tampil lebih dulu
```

### Mengubah lebar widget

```php
protected int | string | array $columnSpan = 'full'; // nilai: 1, 2, atau 'full'
```

---

## Halaman Custom

Halaman yang punya Blade template sendiri (bukan CRUD otomatis):

| File PHP | File Blade |
|---|---|
| `app/Filament/Pages/Login.php` | `resources/views/filament/pages/login.blade.php` |
| `app/Filament/Pages/VehicleCalendar.php` | `resources/views/filament/pages/vehicle-calendar.blade.php` |
| `app/Filament/Pages/TicketReport.php` | `resources/views/filament/pages/ticket-report.blade.php` |

Edit file **Blade** untuk mengubah tampilan HTML.
Edit file **PHP** untuk mengubah judul, navigasi, dan logika.

---

## Tampilan Login

| File | Fungsi |
|---|---|
| `resources/views/filament/pages/login.blade.php` | Tampilan HTML halaman login |
| `app/Filament/Pages/Login.php` | Logika dan konfigurasi login |

---

## Tema & Warna

File: `app/Providers/Filament/AdminPanelProvider.php`

```php
->colors([
    'primary' => Color::Amber, // ganti warna utama di sini
])
```

Pilihan warna bawaan: `Color::Blue`, `Color::Green`, `Color::Red`,
`Color::Amber`, `Color::Indigo`, `Color::Rose`, `Color::Violet`, dll.

Atau gunakan hex custom:
```php
'primary' => Color::hex('#1e40af'),
```

Proyek ini juga menggunakan plugin **Hasnayeen Themes** — tema bisa diubah
langsung dari halaman admin tanpa perlu edit kode.

---

## Komponen Blade

| File | Fungsi |
|---|---|
| `resources/views/filament/components/logout-button.blade.php` | Tombol logout di user menu |
| `resources/views/livewire/ticket-chat.blade.php` | Chat tiket real-time (Livewire) |
| `resources/views/vendor/filament-breezy/` | Override tampilan profil & 2FA |
| `resources/views/vendor/filament-socialite/` | Override tampilan tombol login Google |
| `resources/views/reports/ticket-pdf.blade.php` | Template cetak PDF tiket |

---

## Komponen Livewire

Proyek ini menggunakan Livewire untuk membuat antarmuka yang dinamis dan reaktif menggunakan PHP, tanpa harus menulis JavaScript yang kompleks. Livewire memungkinkan Anda mengembangkan UI secara *full-stack* dengan Laravel.

### Cara Kerja Livewire

Secara sederhana, Livewire bekerja dengan mengintersep interaksi pengguna di browser (misalnya klik tombol, input teks), mengirimkannya sebagai *request* AJAX ke server, memprosesnya dengan PHP, dan kemudian memperbarui hanya bagian UI yang relevan di browser.

### Lokasi File Livewire

Komponen Livewire terdiri dari dua bagian utama:

1.  **Class Komponen (PHP)**: Berisi logika, properti data, dan *event listener*.
    *   `app/Livewire/`
        *   `TicketChat.php`

2.  **View Komponen (Blade)**: Berisi tampilan HTML yang di-*render* oleh komponen.
    *   `resources/views/livewire/`
        *   `ticket-chat.blade.php`

### Cara Membuat Komponen Livewire Baru

Untuk membuat komponen Livewire baru, Anda bisa menggunakan perintah Artisan:

```bash
php artisan make:livewire NamaKomponen
```

Perintah ini akan membuat dua file:
*   `app/Livewire/NamaKomponen.php`
*   `resources/views/livewire/nama-komponen.blade.php`

### Menggunakan Komponen Livewire di Blade View

Anda bisa menyertakan komponen Livewire di Blade view manapun dengan sintaks:

```blade
<livewire:nama-komponen />

{{-- Atau dengan class component (jika ada parameter) --}}
@livewire('nama-komponen', ['parameter' => $nilai])
```

---

## Manajemen Aset (CSS & JavaScript)

Proyek ini menggunakan **Vite** sebagai *build tool* untuk mengelola dan mengkompilasi aset CSS dan JavaScript Anda. Ini memungkinkan pengembangan yang cepat dengan *Hot Module Replacement (HMR)* dan *bundling* yang efisien untuk produksi.

### Struktur File Aset

*   **File Sumber (Source Files)**: Ditemukan di `resources/`
    *   `resources/css/app.css`: File CSS utama aplikasi (menggunakan Tailwind CSS).
    *   `resources/js/app.js`: File JavaScript utama aplikasi.
    *   `resources/js/bootstrap.js`: Berisi konfigurasi awal seperti *import* `axios` atau pustaka lainnya.
*   **File Terkompilasi (Compiled Files)**: Setelah proses *build* Vite, file-file ini akan ditempatkan di `public/build/`.
    *   `public/build/assets/app.css`
    *   `public/build/assets/app.js`

### Konfigurasi Vite

Konfigurasi Vite berada di `vite.config.js`. File ini mendefinisikan *entry points* untuk aset Anda (`resources/css/app.css`, `resources/js/app.js`) dan plugin yang digunakan (misalnya, untuk Laravel dan Blade).

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
                // Tambahkan file CSS/JS lain di sini jika diperlukan
            ],
            refresh: true,
        }),
    ],
});
```

### Mengkompilasi Aset

Untuk menjalankan Vite dan mengkompilasi aset, gunakan perintah berikut di terminal:

*   **Mode Pengembangan (Development Mode)**: Untuk pengembangan dengan HMR.

    ```bash
    npm run dev
    ```

*   **Mode Produksi (Production Mode)**: Untuk *build* final yang dioptimalkan.

    ```bash
    npm run build
    ```

### Menyertakan Aset di Blade View

Di *layout* Blade utama Anda (`resources/views/layouts/app.blade.php` atau sejenisnya), aset Vite disertakan menggunakan direktif Blade `@vite`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Konten aplikasi Anda --}}
</body>
</html>
```
Direktif `@vite` secara otomatis akan mengarah ke aset yang benar (baik dari server pengembangan Vite atau dari file terkompilasi di `public/build` saat produksi).

---

## Referensi Cepat

| Yang Ingin Diubah | File yang Diedit | Bagian |
|---|---|---|
| Label menu sidebar (Resource) | `app/Filament/Resources/NamaResource.php` | Property `$navigationLabel` |
| Label teks "Dashboard" | `lang/vendor/filament-panels/id/pages/dashboard.php` | Key `title` |
| Urutan menu sidebar | `app/Filament/Resources/NamaResource.php` | Property `$navigationSort` |
| Grup menu sidebar | `app/Filament/Resources/NamaResource.php` | Property `$navigationGroup` |
| Ikon menu sidebar | `app/Filament/Resources/NamaResource.php` | Property `$navigationIcon` |
| Kolom di tabel list | `app/Filament/Resources/NamaResource.php` | Method `table()` |
| Field di form tambah/edit | `app/Filament/Resources/NamaResource.php` | Method `form()` |
| Tampilan detail data | `app/Filament/Resources/NamaResource.php` | Method `infolist()` |
| Tampilan widget dashboard | `resources/views/filament/widgets/NamaWidget.blade.php` | HTML Blade |
| Logika/data widget | `app/Filament/Widgets/NamaWidget.php` | Method PHP |
| Tampilan halaman login | `resources/views/filament/pages/login.blade.php` | HTML Blade |
| Warna & tema panel | `app/Providers/Filament/AdminPanelProvider.php` | Method `->colors([...])` |
| Konfigurasi global panel | `app/Providers/Filament/AdminPanelProvider.php` | Method `panel()` |
