<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ArticleExporter;
use App\Filament\Imports\ArticleImporter;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use App\Settings\ModuleSettings; // Import ModuleSettings

class ArticleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?string $modelLabel = 'Artikel';

    protected static ?string $pluralModelLabel = 'Artikel';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return app(ModuleSettings::class)->enable_blog;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category']); // Eager loading untuk performa
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'article:create',
            'article:update',
            'article:delete',
            'article:pagination',
            'article:detail',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Split::make([
                    // Main Content (Left)
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Judul')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),

                            Forms\Components\RichEditor::make('content')
                                ->label('Konten')
                                ->required()
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory('articles/content')
                                ->fileAttachmentsVisibility('public')
                                ->toolbarButtons([
                                    'attachFiles',
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike',
                                    'link',
                                    'orderedList',
                                    'bulletList',
                                    'h2',
                                    'h3',
                                    'blockquote',
                                    'codeBlock',
                                    'redo',
                                    'undo',
                                ]),
                        ]),

                    // Sidebar (Right)
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draf',
                                    'published' => 'Terbit',
                                    'archived' => 'Arsip',
                                ])
                                ->default('draft')
                                ->required()
                                ->native(false),

                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('Tanggal Terbit')
                                ->nullable(),

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug')
                                        ->required(),
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true),
                                ])
                                ->required(),

                            Forms\Components\Select::make('user_id')
                                ->label('Penulis (User)')
                                ->relationship('author', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($state) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('author_name', $user->name);
                                        }
                                    }
                                })
                                ->nullable(),

                            Forms\Components\TextInput::make('author_name')
                                ->label('Alias Penulis')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Nama yang ditampilkan jika berbeda dengan user.'),

                            SpatieMediaLibraryFileUpload::make('featured_image')
                                ->label('Gambar Utama')
                                ->collection('featured')
                                ->image()
                                ->imageEditor()
                                ->columnSpanFull(),
                        ])->grow(false), // Sidebar width fixed/smaller
                ])->from('md')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured')
                    ->label('Gambar')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Article&background=random'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->title),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Penulis')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Terbit',
                        'draft' => 'Draf',
                        'archived' => 'Arsip',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('views')
                    ->label('Dilihat')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Diterbitkan Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draf',
                        'published' => 'Terbit',
                        'archived' => 'Arsip',
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Article $record): string => route('article.show', ['slug' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()->exporter(ArticleExporter::class),
                ImportAction::make()->importer(ArticleImporter::class),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'view' => Pages\ViewArticle::route('/{record}'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Artikel')->schema([
                    TextEntry::make('title')->label('Judul'),
                    TextEntry::make('slug')->label('Slug'),
                    TextEntry::make('author_name')->label('Penulis'),
                    TextEntry::make('category.name')->label('Kategori')->badge(),
                    TextEntry::make('status')->label('Status')->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'published' => 'Terbit',
                            'draft' => 'Draf',
                            'archived' => 'Arsip',
                            default => $state,
                        }),
                    TextEntry::make('views')->label('Dilihat'),
                    TextEntry::make('published_at')->label('Diterbitkan Pada')->dateTime(),
                ])->columns(2),

                Section::make('Konten')->schema([
                    TextEntry::make('content')
                        ->label('Konten')
                        ->html()
                        ->columnSpanFull(),
                ]),

                Section::make('Gambar Utama')->schema([
                    SpatieMediaLibraryImageEntry::make('featured_image')
                        ->label('Gambar Utama')
                        ->collection('featured')
                        ->columnSpanFull(),
                ])->collapsible(),
            ]);
    }
}