<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Settings\ModuleSettings; // Import ModuleSettings

class VehicleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Kendaraan Dinas';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Master Kendaraan';

    protected static ?string $modelLabel = 'Kendaraan';

    protected static ?string $pluralModelLabel = 'Kendaraan';

    // Implement shouldRegisterNavigation method
    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_vehicle_booking;
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


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kendaraan')
                    ->schema([
                        Forms\Components\TextInput::make('plate_number')
                            ->label('Nomor Plat')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Contoh: B 1234 ABC'),

                        Forms\Components\TextInput::make('brand')
                            ->label('Merk')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Toyota, Honda'),

                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Avanza, Innova'),

                        Forms\Components\TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue(date('Y') + 1)
                            ->placeholder('Contoh: 2022'),

                        Forms\Components\TextInput::make('color')
                            ->label('Warna')
                            ->maxLength(50)
                            ->placeholder('Contoh: Hitam, Putih'),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Kapasitas Penumpang')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(20)
                            ->suffix('orang'),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Kendaraan')
                    ->schema([
                        Forms\Components\Select::make('fuel_type')
                            ->label('Jenis BBM')
                            ->options([
                                'bensin' => 'Bensin',
                                'solar' => 'Solar',
                                'listrik' => 'Listrik',
                            ])
                            ->default('bensin')
                            ->required(),

                        Forms\Components\Select::make('ownership')
                            ->label('Status Kepemilikan')
                            ->options([
                                'dinas' => 'Kendaraan Dinas',
                                'sewa' => 'Kendaraan Sewa',
                            ])
                            ->default('dinas')
                            ->required(),

                        Forms\Components\Select::make('condition')
                            ->label('Kondisi')
                            ->options([
                                'excellent' => 'Sangat Baik',
                                'good' => 'Baik',
                                'fair' => 'Cukup',
                                'poor' => 'Buruk',
                            ])
                            ->default('good')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'in_use' => 'Digunakan',
                                'maintenance' => 'Perbaikan',
                                'retired' => 'Tidak Aktif',
                            ])
                            ->default('available')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Tanggal Penting')
                    ->schema([
                        Forms\Components\DatePicker::make('last_service_date')
                            ->label('Tanggal Servis Terakhir'),

                        Forms\Components\DatePicker::make('tax_expiry_date')
                            ->label('Tanggal Pajak Habis'),

                        Forms\Components\DatePicker::make('inspection_expiry_date')
                            ->label('Tanggal KIR Habis'),
                    ])->columns(3),

                Forms\Components\Section::make('Gambar & Catatan')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Foto Kendaraan')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1280')
                            ->imageResizeTargetHeight('720')
                            ->directory('vehicles')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Catatan tambahan tentang kendaraan ini...'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=KDO&background=random'),

                Tables\Columns\TextColumn::make('plate_number')
                    ->label('No. Plat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Merk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->suffix(' org')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('fuel_type')
                    ->label('BBM')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bensin' => 'Bensin',
                        'solar' => 'Solar',
                        'listrik' => 'Listrik',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bensin' => 'success',
                        'solar' => 'warning',
                        'listrik' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'excellent' => 'success',
                        'good' => 'info',
                        'fair' => 'warning',
                        'poor' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'in_use' => 'Digunakan',
                        'maintenance' => 'Perbaikan',
                        'retired' => 'Tidak Aktif',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'in_use' => 'warning',
                        'maintenance' => 'info',
                        'retired' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('tax_expiry_date')
                    ->label('Pajak')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record?->isTaxExpired() ? 'danger' : ($record?->tax_expiry_warning ? 'warning' : null))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'in_use' => 'Digunakan',
                        'maintenance' => 'Perbaikan',
                        'retired' => 'Tidak Aktif',
                    ]),

                Tables\Filters\SelectFilter::make('condition')
                    ->label('Kondisi')
                    ->options([
                        'excellent' => 'Sangat Baik',
                        'good' => 'Baik',
                        'fair' => 'Cukup',
                        'poor' => 'Buruk',
                    ]),

                Tables\Filters\SelectFilter::make('fuel_type')
                    ->label('Jenis BBM')
                    ->options([
                        'bensin' => 'Bensin',
                        'solar' => 'Solar',
                        'listrik' => 'Listrik',
                    ]),

                Tables\Filters\SelectFilter::make('ownership')
                    ->label('Kepemilikan')
                    ->options([
                        'dinas' => 'Kendaraan Dinas',
                        'sewa' => 'Kendaraan Sewa',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Kendaraan')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Foto')
                            ->columnSpanFull()
                            ->height(200),
                        TextEntry::make('plate_number')->label('Nomor Plat')->weight('bold'),
                        TextEntry::make('brand')->label('Merk'),
                        TextEntry::make('model')->label('Model'),
                        TextEntry::make('year')->label('Tahun')->default('-'),
                        TextEntry::make('color')->label('Warna')->default('-'),
                        TextEntry::make('capacity')->label('Kapasitas')->suffix(' orang'),
                    ])->columns(2),

                Section::make('Detail Kendaraan')
                    ->schema([
                        TextEntry::make('fuel_type')
                            ->label('Jenis BBM')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'bensin' => 'Bensin',
                                'solar' => 'Solar',
                                'listrik' => 'Listrik',
                                default => $state,
                            }),
                        TextEntry::make('ownership')
                            ->label('Kepemilikan')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'dinas' => 'Kendaraan Dinas',
                                'sewa' => 'Kendaraan Sewa',
                                default => $state,
                            }),
                        TextEntry::make('condition')
                            ->label('Kondisi')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'excellent' => 'Sangat Baik',
                                'good' => 'Baik',
                                'fair' => 'Cukup',
                                'poor' => 'Buruk',
                                default => $state,
                            }),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'available' => 'Tersedia',
                                'in_use' => 'Digunakan',
                                'maintenance' => 'Perbaikan',
                                'retired' => 'Tidak Aktif',
                                default => $state,
                            }),
                    ])->columns(2),

                Section::make('Tanggal Penting')
                    ->schema([
                        TextEntry::make('last_service_date')->label('Servis Terakhir')->date('d M Y')->placeholder('-'),
                        TextEntry::make('tax_expiry_date')->label('Pajak Habis')->date('d M Y')->placeholder('-'),
                        TextEntry::make('inspection_expiry_date')->label('KIR Habis')->date('d M Y')->placeholder('-'),
                    ])->columns(3),

                Section::make('Catatan')
                    ->schema([
                        TextEntry::make('notes')->label('Catatan')->default('Tidak ada catatan')->columnSpanFull(),
                    ])->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
