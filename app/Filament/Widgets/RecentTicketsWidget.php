<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use App\Settings\ModuleSettings; // Import ModuleSettings

class RecentTicketsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return app(ModuleSettings::class)->enable_helpdesk_tickets && auth()->user()->hasRole('super_admin');
    }

    protected static ?string $heading = 'Widget Tiket Terbuka (Admin)';

    protected function getTableHeading(): string | Htmlable | null
    {
        return new HtmlString('Tiket Terbuka Terbaru');
    }

    protected function getTableDescription(): string | Htmlable | null
    {
        return 'Daftar tiket yang memerlukan penanganan, diurutkan berdasarkan prioritas';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()->with(['user', 'assignedTo'])
                    ->whereIn('status', ['open', 'in_progress', 'waiting_for_user'])
                    ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->heading(
                    new HtmlString('
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                            </svg>
                            <span>Tiket Terbuka Terbaru</span>
                        </div>
                        ')
                    )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('No. Tiket')
                    ->weight('bold')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subjek')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->subject),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelapor')
                    ->limit(15),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->category_label)
                    ->color(fn (string $state): string => match ($state) {
                        'hardware' => 'info',
                        'software' => 'success',
                        'network' => 'warning',
                        'printer' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->priority_label)
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
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'waiting_for_user' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Ditugaskan')
                    ->default('-')
                    ->limit(15),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.tickets.view', $record)),

                Tables\Actions\Action::make('assign')
                    ->label('Tugaskan')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->url(fn ($record) => route('filament.admin.resources.tickets.edit', $record))
                    ->visible(fn ($record) => is_null($record->assigned_to)),
            ])
            ->emptyStateHeading('Tidak ada tiket terbuka')
            ->emptyStateDescription('Semua tiket sudah ditangani.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
