<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\UserResource\Pages;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Infolists\Components\Section as InfolistSection;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use App\Settings\ModuleSettings; // Import ModuleSettings

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Daftar User';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_user_management;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi User')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->numeric()
                            ->minLength(9)
                            ->maxLength(9)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'NIP sudah terdaftar.',
                            ]),
                        TextInput::make('phone_number')
                            ->label('No. HP')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn(string $state): string => bcrypt($state)),
                    ])->columns(2),
            ]);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('avatar_url')
                        ->searchable()
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url
                            ? $record->avatar_url
                            : "https://ui-avatars.com/api/?name=" . urlencode($record->name)),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->label('Nama')
                            ->searchable()
                            ->weight(FontWeight::Bold),
                        Tables\Columns\TextColumn::make('nip')
                            ->label('NIP')
                            ->searchable()
                            ->icon('heroicon-o-identification')
                            ->color('gray')
                            ->size('sm'),
                    ])->space(1),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('roles.name')
                            ->label('Role')
                            ->searchable()
                            ->icon('heroicon-o-shield-check')
                            ->grow(false),
                        Tables\Columns\TextColumn::make('phone_number')
                            ->label('No. HP')
                            ->icon('heroicon-m-phone')
                            ->searchable()
                            ->placeholder('-')
                            ->grow(false),
                        Tables\Columns\TextColumn::make('email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->searchable()
                            ->placeholder('-')
                            ->grow(false),
                    ])->alignStart()->visibleFrom('lg')->space(1)
                ]),
            ])
            ->filters([
                //
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('Set Role')
                    ->label('Atur Role')
                    ->icon('heroicon-m-adjustments-vertical')
                    ->form([
                        Select::make('roles')
                            ->label('Role')
                            ->options(Role::pluck('name', 'id'))
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn ($record) => $record->roles->pluck('id')->toArray()),
                    ])
                    ->action(function (array $data, User $record) {
                        $roles = Role::whereIn('id', $data['roles'])->get();
                        $record->syncRoles($roles);

                        Notification::make()
                            ->title('Role berhasil diperbarui')
                            ->body("Role untuk {$record->name} telah diperbarui.")
                            ->success()
                            ->send();
                    }),
                // Impersonate::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(UserExporter::class),
                ImportAction::make()
                    ->importer(UserImporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assignRoles')
                        ->label('Atur Role')
                        ->icon('heroicon-o-shield-check')
                        ->color('primary')
                        ->form([
                            Select::make('roles')
                                ->label('Pilih Role')
                                ->options(Role::pluck('name', 'id'))
                                ->multiple()
                                ->required()
                                ->searchable()
                                ->preload(),
                            Select::make('mode')
                                ->label('Mode')
                                ->options([
                                    'sync' => 'Ganti semua role (Replace)',
                                    'attach' => 'Tambahkan role (Append)',
                                ])
                                ->default('sync')
                                ->required()
                                ->helperText('Replace: Hapus role lama, ganti dengan yang baru. Append: Tambahkan role tanpa menghapus yang lama.'),
                        ])
                        ->action(function (array $data, $records) {
                            $roles = Role::whereIn('id', $data['roles'])->get();
                            $count = 0;

                            foreach ($records as $user) {
                                if ($data['mode'] === 'sync') {
                                    $user->syncRoles($roles);
                                } else {
                                    $user->assignRole($roles);
                                }
                                $count++;
                            }

                            Notification::make()
                                ->title('Role berhasil diperbarui')
                                ->body("{$count} user telah diperbarui.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                    ->exporter(UserExporter::class)
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Informasi User')
                    ->schema([
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('nip')
                            ->label('NIP')
                            ->icon('heroicon-o-identification'),
                        TextEntry::make('phone_number')
                            ->label('No. HP')
                            ->icon('heroicon-m-phone')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->placeholder('-'),
                        TextEntry::make('roles.name')
                            ->label('Role')
                            ->icon('heroicon-o-shield-check'),
                    ])->columns(2),
            ]);
    }
}
