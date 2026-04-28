<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Device;
use App\Models\Ticket;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use App\Settings\ModuleSettings; // Import ModuleSettings

class TicketResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'IT Helpdesk';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Tiket';

    protected static ?string $modelLabel = 'Tiket';

    protected static ?string $pluralModelLabel = 'Tiket';

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_helpdesk_tickets;
    }

    // Scope: User biasa hanya lihat tiket sendiri, admin lihat semua
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user', 'assignedTo', 'device']); // Eager loading untuk performa

        if (!auth()->user()->hasAnyRole(['super_admin', 'Admin'])) {
            $query->where('user_id', auth()->id());
        }

        return $query;
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
            'assign',
            'resolve',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'Admin']);
        $cacheKey = "ticket_badge_{$userId}_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($isAdmin, $userId) {
            $query = static::getModel()::whereIn('status', ['open', 'in_progress']);

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $count = $query->count();
            return $count ?: null;
        });
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'Admin']);
        $cacheKey = "ticket_badge_color_{$userId}_" . ($isAdmin ? 'admin' : 'user');

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($isAdmin, $userId) {
            $query = static::getModel()::where('status', 'open');

            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $count = $query->count();
            return $count > 5 ? 'danger' : ($count > 0 ? 'warning' : 'success');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tiket')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('Nomor Tiket')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),

                        Forms\Components\Select::make('user_id')
                            ->label('Pelapor')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => auth()->id())
                            ->disabled(fn () => !auth()->user()->hasAnyRole(['super_admin', 'Admin']))
                            ->dehydrated(true), // Pastikan value tetap terkirim meskipun disabled

                        Forms\Components\Select::make('device_id')
                            ->label('Perangkat Terkait')
                            ->options(function (Forms\Get $get) {
                                $userId = $get('user_id');
                                if ($userId) {
                                    return Device::where('user_id', $userId)
                                        ->get()
                                        ->mapWithKeys(fn ($device) => [
                                            $device->id => $device->display_name . ' (' . $device->type . ')'
                                        ]);
                                }
                                return Device::all()->mapWithKeys(fn ($device) => [
                                    $device->id => $device->display_name . ' (' . ($device->user?->name ?? 'Belum di-assign') . ')'
                                ]);
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->helperText('Pilih perangkat yang bermasalah (opsional)')
                            ->disabled(fn ($record, Forms\Get $get) => ($record !== null && !auth()->user()->hasAnyRole(['super_admin', 'Admin'])) || $get('is_external_device'))
                            ->dehydrated(true),

                        Forms\Components\Toggle::make('is_external_device')
                            ->label('Perangkat Luar/Lainnya')
                            ->helperText('Centang jika perangkat yang bermasalah adalah printer, mesin fotokopi, atau perangkat lain di luar PC/Laptop Anda.')
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $state ? $set('device_id', null) : null),

                        Forms\Components\Select::make('category')
                            ->label('Layanan')
                            ->options([
                                'incident_management' => 'Manajemen Insiden',
                                'service_request'     => 'Permintaan Layanan',
                                'user_support'        => 'Dukungan Pengguna',
                                'access_management'   => 'Manajemen Akses',
                                'asset_management'    => 'Manajemen Aset',
                                'change_management'   => 'Manajemen Perubahan',
                                'network_support'     => 'Dukungan Jaringan',
                                'security_support'    => 'Dukungan Keamanan',
                                'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                            ])
                            ->required()
                            ->default('incident_management')
                            ->disabled(fn ($record) => $record !== null && !auth()->user()->hasAnyRole(['super_admin', 'Admin']))
                            ->dehydrated(true),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options(function () {
                                $options = [
                                    'low' => 'Rendah',
                                    'medium' => 'Sedang',
                                    'high' => 'Tinggi',
                                ];
                                // Hanya admin yang bisa set prioritas Kritis
                                if (auth()->user()->hasAnyRole(['super_admin', 'Admin'])) {
                                    $options['critical'] = 'Kritis';
                                }
                                return $options;
                            })
                            ->required()
                            ->default('medium'),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Masalah')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ringkasan singkat masalah')
                            ->disabled(fn ($record) => $record !== null && !auth()->user()->hasAnyRole(['super_admin', 'Admin']))
                            ->dehydrated(true),

                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->placeholder('Jelaskan masalah secara detail...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record !== null && !auth()->user()->hasAnyRole(['super_admin', 'Admin']))
                            ->dehydrated(true),

                        Forms\Components\FileUpload::make('attachments')
                            ->label('Lampiran Foto/File')
                            ->multiple()
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120) // 5MB
                            ->maxFiles(5)
                            ->disk('public')
                            ->directory('ticket-attachments')
                            ->visibility('public')
                            ->helperText('Upload foto kerusakan atau file pendukung (maks. 5 file, @5MB)')
                            ->columnSpanFull()
                            ->dehydrated(false),
                    ]),

                Forms\Components\Section::make('Penanganan')
                    ->schema([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Ditugaskan Ke')
                            ->options(function () {
                                return User::role('Admin')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('IT Admin yang menangani tiket'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Dibuka',
                                'in_progress' => 'Diproses',
                                'waiting_for_user' => 'Menunggu User',
                                'resolved' => 'Selesai',
                                'closed' => 'Ditutup',
                            ])
                            ->required()
                            ->default('open')
                            ->reactive(),

                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Catatan Penyelesaian')
                            ->placeholder('Jelaskan solusi atau tindakan yang diambil...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['resolved', 'closed'])),
                    ])->columns(2)
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'Admin'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->subject),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelapor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Layanan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'incident_management' => 'Manajemen Insiden',
                        'service_request'     => 'Permintaan Layanan',
                        'user_support'        => 'Dukungan Pengguna',
                        'access_management'   => 'Manajemen Akses',
                        'asset_management'    => 'Manajemen Aset',
                        'change_management'   => 'Manajemen Perubahan',
                        'network_support'     => 'Dukungan Jaringan',
                        'security_support'    => 'Dukungan Keamanan',
                        'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'incident_management' => 'danger',
                        'service_request'     => 'info',
                        'user_support'        => 'success',
                        'access_management'   => 'warning',
                        'asset_management'    => 'primary',
                        'change_management'   => 'warning',
                        'network_support'     => 'info',
                        'security_support'    => 'danger',
                        'documentation_kb'    => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'critical' => 'Kritis',
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'open' => 'Dibuka',
                        'in_progress' => 'Diproses',
                        'waiting_for_user' => 'Menunggu User',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'waiting_for_user' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Ditugaskan')
                    ->default('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('device.hostname')
                    ->label('Perangkat')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Diselesaikan')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Dibuka',
                        'in_progress' => 'Diproses',
                        'waiting_for_user' => 'Menunggu User',
                        'resolved' => 'Selesai',
                        'closed' => 'Ditutup',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'critical' => 'Kritis',
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Layanan')
                    ->options([
                        'incident_management' => 'Manajemen Insiden',
                        'service_request'     => 'Permintaan Layanan',
                        'user_support'        => 'Dukungan Pengguna',
                        'access_management'   => 'Manajemen Akses',
                        'asset_management'    => 'Manajemen Aset',
                        'change_management'   => 'Manajemen Perubahan',
                        'network_support'     => 'Dukungan Jaringan',
                        'security_support'    => 'Dukungan Keamanan',
                        'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                    ]),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Ditugaskan Ke')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pelapor')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('unassigned')
                    ->label('Belum Ditugaskan')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_to'))
                    ->toggle(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('assign')
                    ->label('Tugaskan')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Tugaskan Ke')
                            ->options(function () {
                                return User::role('Admin')
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'in_progress',
                        ]);
                    })
                    ->visible(fn (Ticket $record) => $record->status === 'open' && auth()->user()->hasAnyRole(['super_admin', 'Admin'])),

                Tables\Actions\Action::make('resolve')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('resolution_notes')
                            ->label('Catatan Penyelesaian')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        $record->markAsResolved($data['resolution_notes']);
                    })
                    ->visible(fn (Ticket $record) => in_array($record->status, ['open', 'in_progress', 'waiting_for_user']) && auth()->user()->hasAnyRole(['super_admin', 'Admin'])),

                Tables\Actions\Action::make('close')
                    ->label('Tutup')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (Ticket $record) => $record->close())
                    ->visible(fn (Ticket $record) => $record->status === 'resolved'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'Admin'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'Admin'])),
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
                Section::make('Informasi Tiket')
                    ->schema([
                        TextEntry::make('ticket_number')
                            ->label('Nomor Tiket')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('user.name')
                            ->label('Pelapor'),
                        TextEntry::make('category')
                            ->label('Layanan')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'incident_management' => 'Manajemen Insiden',
                                'service_request'     => 'Permintaan Layanan',
                                'user_support'        => 'Dukungan Pengguna',
                                'access_management'   => 'Manajemen Akses',
                                'asset_management'    => 'Manajemen Aset',
                                'change_management'   => 'Manajemen Perubahan',
                                'network_support'     => 'Dukungan Jaringan',
                                'security_support'    => 'Dukungan Keamanan',
                                'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                                default => $state,
                            }),
                        TextEntry::make('priority')
                            ->label('Prioritas')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'critical' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match($state) {
                                'critical' => 'Kritis',
                                'high' => 'Tinggi',
                                'medium' => 'Sedang',
                                'low' => 'Rendah',
                                default => $state,
                            }),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state) => match($state) {
                                'open' => 'danger',
                                'in_progress' => 'warning',
                                'waiting_for_user' => 'info',
                                'resolved' => 'success',
                                'closed' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match($state) {
                                'open' => 'Dibuka',
                                'in_progress' => 'Diproses',
                                'waiting_for_user' => 'Menunggu User',
                                'resolved' => 'Selesai',
                                'closed' => 'Ditutup',
                                default => $state,
                            }),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M Y H:i'),
                    ])->columns(3),

                Section::make('Detail Masalah')
                    ->schema([
                        TextEntry::make('subject')
                            ->label('Subjek'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Lampiran')
                    ->schema([
                        TextEntry::make('attachments')
                            ->label('')
                            ->state(function ($record) {
                                return $record->attachments->count() > 0 ? 'has_attachments' : null;
                            })
                            ->formatStateUsing(fn ($record) => view('filament.resources.ticket-resource.partials.attachments', ['attachments' => $record->attachments]))
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->attachments->count() > 0),

                Section::make('Perangkat Terkait')
                    ->schema([
                        TextEntry::make('is_external_device')
                            ->label('Jenis Perangkat')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Perangkat Luar/Lainnya' : 'Perangkat Terdaftar')
                            ->color(fn ($state) => $state ? 'warning' : 'success'),
                        TextEntry::make('device.display_name')
                            ->label('Perangkat')
                            ->default('Tidak ada')
                            ->visible(fn ($record) => !$record->is_external_device),
                        TextEntry::make('device.type')
                            ->label('Tipe')
                            ->badge()
                            ->default('-')
                            ->visible(fn ($record) => !$record->is_external_device),
                        TextEntry::make('device.serial_number')
                            ->label('Serial Number')
                            ->default('-')
                            ->visible(fn ($record) => !$record->is_external_device),
                        TextEntry::make('device.ip_address')
                            ->label('IP Address')
                            ->default('-')
                            ->visible(fn ($record) => !$record->is_external_device),
                    ])->columns(4)
                    ->visible(fn ($record) => $record->device_id !== null || $record->is_external_device),

                Section::make('Penanganan')
                    ->schema([
                        TextEntry::make('assignedTo.name')
                            ->label('Ditugaskan Ke')
                            ->default('Belum ditugaskan'),
                        TextEntry::make('resolved_at')
                            ->label('Diselesaikan')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('closed_at')
                            ->label('Ditutup')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('resolution_notes')
                            ->label('Catatan Penyelesaian')
                            ->default('Belum ada')
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // ResponsesRelationManager diganti dengan TicketChatWidget
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'          => Pages\ListTickets::route('/'),
            'select-service' => Pages\SelectTicketService::route('/select-service'),
            'create'         => Pages\CreateTicket::route('/create'),
            'view'           => Pages\ViewTicket::route('/{record}'),
            'edit'           => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
