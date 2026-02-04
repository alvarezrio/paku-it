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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeviceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Device';

    protected static ?string $modelLabel = 'Device';

    protected static ?string $pluralModelLabel = 'Device';

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
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Device')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'laptop' => 'Laptop',
                                'desktop' => 'Desktop',
                                'all-in-one' => 'All-in-One',
                                'workstation' => 'Workstation',
                            ])
                            ->required()
                            ->default('desktop'),

                        Forms\Components\Select::make('user_id')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Kosongkan jika device belum digunakan'),

                        Forms\Components\TextInput::make('hostname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('cth: PC150421XXX'),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->required()
                            ->ip()
                            ->maxLength(45)
                            ->placeholder('cth: 10.9.1.XXX'),

                        Forms\Components\TextInput::make('mac_address')
                            ->label('MAC Address')
                            ->maxLength(17)
                            ->placeholder('cth: 00:1A:2B:3C:4D:5E'),

                        Forms\Components\TextInput::make('brand')
                            ->label('Merek')
                            ->maxLength(255)
                            ->placeholder('cth: Dell, HP, Lenovo'),

                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255)
                            ->placeholder('cth: Latitude 5520'),

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
                            ->placeholder('cth: Lantai 2, Ruang A'),
                    ])->columns(2),

                Forms\Components\Section::make('Spesifikasi Sistem')
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
                                'SSD' => 'SSD',
                                'HDD' => 'HDD',
                                'NVMe' => 'NVMe',
                                'Hybrid' => 'Hybrid',
                            ])
                            ->placeholder('Pilih tipe penyimpanan'),

                        Forms\Components\TextInput::make('storage_capacity')
                            ->label('Kapasitas Penyimpanan')
                            ->maxLength(255)
                            ->placeholder('cth: 512GB'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Tanggal')
                    ->schema([
                        Forms\Components\Select::make('condition')
                            ->label('Kondisi')
                            ->options([
                                'excellent' => 'Sangat Baik',
                                'good' => 'Baik',
                                'fair' => 'Cukup',
                                'poor' => 'Buruk',
                                'broken' => 'Rusak',
                            ])
                            ->default('good')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif',
                                'maintenance' => 'Perbaikan',
                                'retired' => 'Pensiun',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Tanggal Pembelian'),

                        Forms\Components\DatePicker::make('warranty_expiry')
                            ->label('Habis Masa Garansi'),
                    ])->columns(2),

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

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Catatan tambahan tentang device ini...'),
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
                    ->color(fn ($state) => match ($state) {
                        'laptop' => 'info',
                        'desktop' => 'success',
                        'all-in-one' => 'warning',
                        'workstation' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->default('Belum Ada')
                    ->badge()
                    ->color(fn ($state) => $state === 'Belum Ada' ? 'gray' : 'success'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('No. Seri')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('os')
                    ->label('OS')
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ram')
                    ->label('RAM')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'laptop' => 'Laptop',
                        'desktop' => 'Desktop',
                        'all-in-one' => 'All-in-One',
                        'workstation' => 'Workstation',
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

                Tables\Filters\TernaryFilter::make('assigned')
                    ->label('Status Penggunaan')
                    ->placeholder('Semua')
                    ->trueLabel('Digunakan')
                    ->falseLabel('Belum Digunakan')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                    ),
            ])
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
                Section::make('Informasi Device')->schema([
                    TextEntry::make('type')
                        ->label('Tipe')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'laptop' => 'info',
                            'desktop' => 'success',
                            'all-in-one' => 'warning',
                            'workstation' => 'primary',
                            default => 'gray',
                        }),
                    TextEntry::make('user.name')->label('Pengguna')->default('Belum Ada'),
                    TextEntry::make('hostname')->default('-'),
                    TextEntry::make('ip_address')->label('IP Address')->default('-'),
                    TextEntry::make('mac_address')->label('MAC Address')->default('-'),
                    TextEntry::make('brand')->label('Merek')->default('-'),
                    TextEntry::make('model')->label('Model')->default('-'),
                    TextEntry::make('serial_number')->label('Nomor Seri')->default('-'),
                    TextEntry::make('asset_tag')->label('Tag Aset')->default('-'),
                    TextEntry::make('location')->label('Lokasi')->default('-'),
                ])->columns(2),

                Section::make('Spesifikasi Sistem')->schema([
                    TextEntry::make('os')->label('Sistem Operasi')->default('-'),
                    TextEntry::make('os_version')->label('Versi OS')->default('-'),
                    TextEntry::make('processor')->label('Prosesor')->default('-'),
                    TextEntry::make('ram')->label('RAM')->default('-'),
                    TextEntry::make('storage_type')->label('Tipe Penyimpanan')->default('-'),
                    TextEntry::make('storage_capacity')->label('Kapasitas Penyimpanan')->default('-'),
                ])->columns(2),

                Section::make('Status & Tanggal')->schema([
                    TextEntry::make('condition')
                        ->label('Kondisi')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'excellent' => 'Sangat Baik',
                            'good' => 'Baik',
                            'fair' => 'Cukup',
                            'poor' => 'Buruk',
                            'broken' => 'Rusak',
                            default => $state,
                        }),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) => match ($state) {
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'maintenance' => 'Perbaikan',
                            'retired' => 'Pensiun',
                            default => $state,
                        }),
                    TextEntry::make('purchase_date')->label('Tanggal Pembelian')->date(),
                    TextEntry::make('warranty_expiry')->label('Habis Masa Garansi')->date(),
                ])->columns(2),

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
