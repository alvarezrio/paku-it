<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAttributeResource\Pages;
use App\Models\DeviceAttribute;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Settings\ModuleSettings; // Import ModuleSettings

class DeviceAttributeResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DeviceAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Inventaris';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Setting Informasi Perangkat';

    protected static ?string $modelLabel = 'Setting Informasi Perangkat';

    protected static ?string $pluralModelLabel = 'Setting Informasi Perangkat';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Atribut')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->placeholder('cth: GPU, Ukuran Monitor, Kunci Lisensi'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Dibuat otomatis dari nama'),

                        Forms\Components\Select::make('type')
                            ->label('Tipe Input')
                            ->options([
                                'text' => 'Teks',
                                'number' => 'Angka',
                                'select' => 'Dropdown Select',
                                'boolean' => 'Toggle Ya/Tidak',
                                'date' => 'Tanggal',
                                'textarea' => 'Area Teks',
                            ])
                            ->required()
                            ->default('text')
                            ->live()
                            ->helperText('Pilih tipe input untuk atribut ini'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil akan muncul lebih awal'),
                    ])->columns(2),

                Forms\Components\Section::make('Opsi Dropdown')
                    ->schema([
                        Forms\Components\TagsInput::make('options')
                            ->label('Opsi')
                            ->placeholder('Tambah opsi dan tekan Enter')
                            ->helperText('Masukkan setiap opsi untuk dropdown'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'select'),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_required')
                            ->label('Wajib Diisi')
                            ->helperText('Jika diaktifkan, field ini harus diisi saat membuat/mengedit device'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Atribut tidak aktif tidak akan muncul di form device'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => 'Teks',
                        'number' => 'Angka',
                        'select' => 'Dropdown',
                        'boolean' => 'Boolean',
                        'date' => 'Tanggal',
                        'textarea' => 'Area Teks',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'info',
                        'select' => 'warning',
                        'boolean' => 'success',
                        'date' => 'primary',
                        'textarea' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('values_count')
                    ->label('Digunakan Di')
                    ->counts('values')
                    ->suffix(' device'),

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
                        'text' => 'Teks',
                        'number' => 'Angka',
                        'select' => 'Dropdown Select',
                        'boolean' => 'Toggle Ya/Tidak',
                        'date' => 'Tanggal',
                        'textarea' => 'Area Teks',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Status Wajib'),
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
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Atribut')->schema([
                    TextEntry::make('name')->label('Nama'),
                    TextEntry::make('slug')->label('Slug'),
                    TextEntry::make('type')->label('Tipe')->badge(),
                    TextEntry::make('sort_order')->label('Urutan'),
                ])->columns(2),

                Section::make('Opsi')->schema([
                    TextEntry::make('options')
                        ->badge()
                        ->separator(',')
                        ->default('Tidak ada opsi'),
                ])->visible(fn ($record) => $record->type === 'select'),

                Section::make('Pengaturan')->schema([
                    TextEntry::make('is_required')
                        ->label('Wajib')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                    TextEntry::make('is_active')
                        ->label('Aktif')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                        ->color(fn ($state) => $state ? 'success' : 'gray'),
                ])->columns(2),
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
            'index' => Pages\ListDeviceAttributes::route('/'),
            'create' => Pages\CreateDeviceAttribute::route('/create'),
            'view' => Pages\ViewDeviceAttribute::route('/{record}'),
            'edit' => Pages\EditDeviceAttribute::route('/{record}/edit'),
        ];
    }
}
