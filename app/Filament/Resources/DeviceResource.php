<?php

namespace App\Filament\Resources;

use App\Filament\Exports\DeviceExporter;
use App\Filament\Imports\DeviceImporter;
use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use App\Models\DeviceAttribute;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Settings\ModuleSettings; // Import ModuleSettings

class DeviceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Daftar Perangkat';

    protected static ?string $modelLabel = 'Device';

    protected static ?string $pluralModelLabel = 'Device';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_inventory;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user']) // Eager loading untuk performa
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc'); // Secondary sort untuk konsistensi pagination
    }


    public static function form(Form $form): Form
    {
        // Kelompok tipe untuk kondisi visibilitas
        $isKomputer = fn (Forms\Get $get) => in_array($get('type'), ['laptop', 'desktop', 'all-in-one', 'workstation']);
        $isPrinter  = fn (Forms\Get $get) => in_array($get('type'), ['printer', 'scanner']);
        $isJaringan = fn (Forms\Get $get) => in_array($get('type'), ['router', 'switch', 'access-point']);
        $hasNetwork = fn (Forms\Get $get) => in_array($get('type'), ['laptop', 'desktop', 'all-in-one', 'workstation', 'printer', 'scanner', 'router', 'switch', 'access-point']);

        return $form
            ->schema([
                // ── SECTION 1: Informasi Dasar (semua tipe) ─────────────────
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Perangkat')
                            ->options([
                                'Komputer' => [
                                    'laptop'      => 'Laptop',
                                    'desktop'     => 'Desktop',
                                    'all-in-one'  => 'All-in-One',
                                    'workstation' => 'Workstation',
                                ],
                                'Printer / Scanner' => [
                                    'printer' => 'Printer',
                                    'scanner' => 'Scanner',
                                ],
                                'Perangkat Jaringan' => [
                                    'router'       => 'Router',
                                    'switch'       => 'Switch',
                                    'access-point' => 'Access Point',
                                ],
                                'Lainnya' => [
                                    'other' => 'Lainnya',
                                ],
                            ])
                            ->required()
                            ->default('desktop')
                            ->live(), // reactive — form berubah saat tipe diganti

                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Kosongkan jika perangkat belum digunakan'),

                        Forms\Components\TextInput::make('hostname')
                            ->label(fn (Forms\Get $get) => match (true) {
                                in_array($get('type'), ['printer', 'scanner']) => 'Nama Printer/Scanner',
                                in_array($get('type'), ['other'])              => 'Nama Perangkat',
                                default                                        => 'Hostname',
                            })
                            ->required()
                            ->maxLength(255)
                            ->placeholder(fn (Forms\Get $get) => match (true) {
                                in_array($get('type'), ['printer', 'scanner']) => 'cth: Printer-LT2, Canon-MX',
                                in_array($get('type'), ['other'])              => 'cth: Proyektor-R1',
                                default                                        => 'cth: PC150421XXX',
                            }),

                        Forms\Components\TextInput::make('brand')
                            ->label('Merek')
                            ->maxLength(255)
                            ->placeholder(fn (Forms\Get $get) => match (true) {
                                in_array($get('type'), ['printer', 'scanner']) => 'cth: Canon, Epson, HP',
                                in_array($get('type'), ['router', 'switch', 'access-point']) => 'cth: Cisco, TP-Link, Mikrotik',
                                default => 'cth: Dell, HP, Lenovo',
                            }),

                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255)
                            ->placeholder('cth: MX922, RB750, Latitude 5520'),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('cth: ABC123XYZ'),

                        Forms\Components\TextInput::make('asset_tag')
                            ->label('Tag Aset')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('cth: AST-2024-001'),

                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->datalist(['Lantai 1', 'Lantai 2', 'Lantai 3', 'Lantai 4', 'Basement'])
                            ->placeholder('cth: Lantai 2'),

                        Forms\Components\TextInput::make('responsible_section')
                            ->label('Penanggung Jawab')
                            ->maxLength(255)
                            ->placeholder('cth: Seksi Pelayanan, Subbagian Umum, Seksi PDI'),
                    ])->columns(2),

                // ── SECTION 2: Koneksi Jaringan (komputer + perangkat jaringan) ──
                Forms\Components\Section::make('Koneksi Jaringan')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->ip()
                            ->maxLength(45)
                            ->required(fn (Forms\Get $get) => in_array($get('type'), ['router', 'switch', 'access-point']))
                            ->placeholder('cth: 10.9.1.XXX'),

                        Forms\Components\TextInput::make('mac_address')
                            ->label('MAC Address')
                            ->maxLength(17)
                            ->placeholder('cth: 00:1A:2B:3C:4D:5E'),
                    ])
                    ->columns(2)
                    ->visible($hasNetwork),

                // ── SECTION 3: Spesifikasi Komputer (laptop/desktop/dll) ─────
                Forms\Components\Section::make('Spesifikasi Komputer')
                    ->schema([
                        Forms\Components\TextInput::make('os')
                            ->label('Sistem Operasi')
                            ->maxLength(255)
                            ->placeholder('cth: Windows 11 Pro'),

                        Forms\Components\TextInput::make('os_version')
                            ->label('Versi OS')
                            ->maxLength(255)
                            ->placeholder('cth: 22H2'),

                        Forms\Components\TextInput::make('processor')
                            ->label('Prosesor')
                            ->maxLength(255)
                            ->placeholder('cth: Intel Core i7-1165G7'),

                        Forms\Components\TextInput::make('ram')
                            ->label('RAM')
                            ->maxLength(255)
                            ->placeholder('cth: 16GB DDR4'),

                        Forms\Components\Select::make('storage_type')
                            ->label('Tipe Penyimpanan')
                            ->options([
                                'SSD'    => 'SSD',
                                'HDD'    => 'HDD',
                                'NVMe'   => 'NVMe',
                                'Hybrid' => 'Hybrid',
                            ])
                            ->placeholder('Pilih tipe penyimpanan'),

                        Forms\Components\TextInput::make('storage_capacity')
                            ->label('Kapasitas Penyimpanan')
                            ->maxLength(255)
                            ->placeholder('cth: 512GB'),
                    ])
                    ->columns(2)
                    ->visible($isKomputer),

                // ── SECTION 4: Spesifikasi Printer / Scanner ─────────────────
                Forms\Components\Section::make('Spesifikasi Printer / Scanner')
                    ->schema([
                        Forms\Components\Select::make('printer_connection')
                            ->label('Jenis Koneksi')
                            ->options([
                                'USB'      => 'USB',
                                'Network'  => 'Jaringan (LAN)',
                                'Wireless' => 'Wireless / WiFi',
                                'Bluetooth'=> 'Bluetooth',
                            ])
                            ->placeholder('Pilih jenis koneksi')
                            ->helperText('Cara printer terhubung ke komputer'),

                        Forms\Components\TextInput::make('printer_function')
                            ->label('Fungsi')
                            ->placeholder('cth: Print, Scan, Copy, Fax')
                            ->helperText('Fungsi yang didukung perangkat')
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->visible($isPrinter),

                // ── SECTION 5: Status & Tanggal (semua tipe) ─────────────────
                Forms\Components\Section::make('Status & Tanggal')
                    ->schema([
                        Forms\Components\Select::make('condition')
                            ->label('Kondisi')
                            ->options([
                                'excellent' => 'Sangat Baik',
                                'good'      => 'Baik',
                                'fair'      => 'Cukup',
                                'poor'      => 'Buruk',
                                'broken'    => 'Rusak',
                            ])
                            ->default('good')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active'      => 'Aktif',
                                'inactive'    => 'Nonaktif',
                                'maintenance' => 'Perbaikan',
                                'retired'     => 'Pensiun',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Tanggal Pembelian'),

                        Forms\Components\DatePicker::make('warranty_expiry')
                            ->label('Habis Masa Garansi'),
                    ])->columns(2),

                // ── SECTION 6: Atribut Tambahan (dari DeviceAttribute) ────────
                Forms\Components\Section::make('Atribut Tambahan')
                    ->schema(function () {
                        $attributes = DeviceAttribute::active()->ordered()->get();
                        $fields = [];

                        foreach ($attributes as $attribute) {
                            $field = match ($attribute->type) {
                                'text' => Forms\Components\TextInput::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->required($attribute->is_required),
                                'number' => Forms\Components\TextInput::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->numeric()
                                    ->required($attribute->is_required),
                                'textarea' => Forms\Components\Textarea::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->required($attribute->is_required),
                                'select' => Forms\Components\Select::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->options(collect($attribute->options ?? [])->mapWithKeys(fn ($opt) => [$opt => $opt])->toArray())
                                    ->required($attribute->is_required),
                                'boolean' => Forms\Components\Toggle::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->required($attribute->is_required),
                                'date' => Forms\Components\DatePicker::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->required($attribute->is_required),
                                default => Forms\Components\TextInput::make("dynamic_attributes.{$attribute->slug}")
                                    ->label($attribute->name)
                                    ->required($attribute->is_required),
                            };

                            $fields[] = $field;
                        }

                        return $fields;
                    })
                    ->columns(2)
                    ->visible(fn () => DeviceAttribute::active()->exists()),

                // ── SECTION 7: Catatan ────────────────────────────────────────
                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Catatan tambahan tentang perangkat ini...'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hostname')
                    ->label('Hostname')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'laptop'       => 'Laptop',
                        'desktop'      => 'Desktop',
                        'all-in-one'   => 'All-in-One',
                        'workstation'  => 'Workstation',
                        'printer'      => 'Printer',
                        'scanner'      => 'Scanner',
                        'router'       => 'Router',
                        'switch'       => 'Switch',
                        'access-point' => 'Access Point',
                        'other'        => 'Lainnya',
                        default        => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'laptop', 'desktop', 'all-in-one', 'workstation' => 'info',
                        'printer', 'scanner'                              => 'warning',
                        'router', 'switch', 'access-point'               => 'success',
                        default                                           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->default('Belum Ada')
                    ->badge()
                    ->color(fn ($state) => $state === 'Belum Ada' ? 'gray' : 'success'),

                Tables\Columns\TextColumn::make('responsible_section')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('No. Seri')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('os')
                    ->label('OS')
                    ->limit(20)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ram')
                    ->label('RAM')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                        'broken' => 'Rusak',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'excellent' => 'success',
                        'good' => 'info',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        'broken' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        'maintenance' => 'Perbaikan',
                        'retired' => 'Pensiun',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'maintenance' => 'warning',
                        'retired' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('warranty_expiry')
                    ->label('Garansi')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record?->isWarrantyExpired() ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('hostname')
                    ->label('Hostname')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Hostname')
                            ->placeholder('cari hostname...')
                            ->live(debounce: 400),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $q, $v) => $q->where('hostname', 'like', "%{$v}%"))),

                Tables\Filters\Filter::make('ip_address')
                    ->label('IP Address')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('IP Address')
                            ->placeholder('cth: 10.9.1')
                            ->live(debounce: 400),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $q, $v) => $q->where('ip_address', 'like', "%{$v}%"))),

                Tables\Filters\Filter::make('brand_model')
                    ->label('Merek / Model')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Merek atau Model')
                            ->placeholder('cth: Lenovo, OptiPlex')
                            ->live(debounce: 400),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $q, $v) => $q->where(fn ($qq) => $qq
                            ->where('brand', 'like', "%{$v}%")
                            ->orWhere('model', 'like', "%{$v}%")))),

                Tables\Filters\Filter::make('serial_number')
                    ->label('No. Seri / Tag Aset')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('No. Seri / Tag Aset')
                            ->placeholder('cth: SN-, AST-')
                            ->live(debounce: 400),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['value'] ?? null, fn (Builder $q, $v) => $q->where(fn ($qq) => $qq
                            ->where('serial_number', 'like', "%{$v}%")
                            ->orWhere('asset_tag', 'like', "%{$v}%")))),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Perangkat')
                    ->options([
                        'laptop'       => 'Laptop',
                        'desktop'      => 'Desktop',
                        'all-in-one'   => 'All-in-One',
                        'workstation'  => 'Workstation',
                        'printer'      => 'Printer',
                        'scanner'      => 'Scanner',
                        'router'       => 'Router',
                        'switch'       => 'Switch',
                        'access-point' => 'Access Point',
                        'other'        => 'Lainnya',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        'maintenance' => 'Perbaikan',
                        'retired' => 'Pensiun',
                    ]),

                Tables\Filters\SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                        'broken' => 'Rusak',
                    ]),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('location')
                    ->label('Lokasi')
                    ->options(fn () => Device::query()->pluck('location', 'location')->unique()->filter()->sort()),

                Tables\Filters\SelectFilter::make('responsible_section')
                    ->label('Penanggung Jawab')
                    ->options(fn () => Device::query()->pluck('responsible_section', 'responsible_section')->unique()->filter()->sort()),

                Tables\Filters\TernaryFilter::make('assigned')
                    ->label('Status Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum Digunakan')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                    ),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->deferFilters(false)
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Device $record): string => self::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(DeviceExporter::class)
                    ->label('Ekspor')
                    ->icon('heroicon-o-arrow-down-tray'),
                ImportAction::make()
                    ->importer(DeviceImporter::class)
                    ->label('Impor')
                    ->icon('heroicon-o-arrow-up-tray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── Informasi Dasar ───────────────────────────────────────────
                Section::make('Informasi Dasar')->schema([
                    TextEntry::make('type')
                        ->label('Tipe')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'laptop'       => 'Laptop',
                            'desktop'      => 'Desktop',
                            'all-in-one'   => 'All-in-One',
                            'workstation'  => 'Workstation',
                            'printer'      => 'Printer',
                            'scanner'      => 'Scanner',
                            'router'       => 'Router',
                            'switch'       => 'Switch',
                            'access-point' => 'Access Point',
                            'other'        => 'Lainnya',
                            default        => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'laptop', 'desktop', 'all-in-one', 'workstation' => 'info',
                            'printer', 'scanner'                              => 'warning',
                            'router', 'switch', 'access-point'               => 'success',
                            default                                           => 'gray',
                        }),
                    TextEntry::make('user.name')->label('Pengguna')->default('Belum Ada'),
                    TextEntry::make('hostname')->label('Hostname / Nama')->default('-'),
                    TextEntry::make('brand')->label('Merek')->default('-'),
                    TextEntry::make('model')->label('Model')->default('-'),
                    TextEntry::make('serial_number')->label('Nomor Seri')->default('-'),
                    TextEntry::make('asset_tag')->label('Tag Aset')->default('-'),
                    TextEntry::make('location')->label('Lokasi')->default('-'),
                    TextEntry::make('responsible_section')->label('Penanggung Jawab')->default('-'),
                ])->columns(2),

                // ── Koneksi Jaringan (komputer + perangkat jaringan) ─────────
                Section::make('Koneksi Jaringan')->schema([
                    TextEntry::make('ip_address')->label('IP Address')->default('-'),
                    TextEntry::make('mac_address')->label('MAC Address')->default('-'),
                ])->columns(2)
                    ->visible(fn ($record) => in_array($record->type, [
                        'laptop', 'desktop', 'all-in-one', 'workstation',
                        'printer', 'scanner',
                        'router', 'switch', 'access-point',
                    ])),

                // ── Spesifikasi Komputer ──────────────────────────────────────
                Section::make('Spesifikasi Komputer')->schema([
                    TextEntry::make('os')->label('Sistem Operasi')->default('-'),
                    TextEntry::make('os_version')->label('Versi OS')->default('-'),
                    TextEntry::make('processor')->label('Prosesor')->default('-'),
                    TextEntry::make('ram')->label('RAM')->default('-'),
                    TextEntry::make('storage_type')->label('Tipe Penyimpanan')->default('-'),
                    TextEntry::make('storage_capacity')->label('Kapasitas Penyimpanan')->default('-'),
                ])->columns(2)
                    ->visible(fn ($record) => in_array($record->type, [
                        'laptop', 'desktop', 'all-in-one', 'workstation',
                    ])),

                // ── Spesifikasi Printer / Scanner ─────────────────────────────
                Section::make('Spesifikasi Printer / Scanner')->schema([
                    TextEntry::make('printer_connection')
                        ->label('Jenis Koneksi')
                        ->default('-')
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'USB'       => 'USB',
                            'Network'   => 'Jaringan (LAN)',
                            'Wireless'  => 'Wireless / WiFi',
                            'Bluetooth' => 'Bluetooth',
                            default     => $state,
                        }),
                    TextEntry::make('printer_function')->label('Fungsi')->default('-'),
                ])->columns(2)
                    ->visible(fn ($record) => in_array($record->type, ['printer', 'scanner'])),

                // ── Status & Tanggal ──────────────────────────────────────────
                Section::make('Status & Tanggal')->schema([
                    TextEntry::make('condition')
                        ->label('Kondisi')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'excellent' => 'Sangat Baik',
                            'good'      => 'Baik',
                            'fair'      => 'Cukup',
                            'poor'      => 'Buruk',
                            'broken'    => 'Rusak',
                            default     => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'excellent' => 'success',
                            'good'      => 'info',
                            'fair'      => 'warning',
                            'poor', 'broken' => 'danger',
                            default     => 'gray',
                        }),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'active'      => 'Aktif',
                            'inactive'    => 'Nonaktif',
                            'maintenance' => 'Perbaikan',
                            'retired'     => 'Pensiun',
                            default       => $state,
                        })
                        ->color(fn ($state) => match ($state) {
                            'active'      => 'success',
                            'inactive'    => 'gray',
                            'maintenance' => 'warning',
                            'retired'     => 'danger',
                            default       => 'gray',
                        }),
                    TextEntry::make('purchase_date')->label('Tanggal Pembelian')->date()->default('-'),
                    TextEntry::make('warranty_expiry')->label('Habis Masa Garansi')->date()->default('-'),
                ])->columns(2),

                // ── Catatan ───────────────────────────────────────────────────
                Section::make('Catatan')->schema([
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->columnSpanFull()
                        ->default('Tidak ada catatan'),
                ])->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttributeValuesRelationManager::class,
            RelationManagers\TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'view' => Pages\ViewDevice::route('/{record}'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
