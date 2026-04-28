<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogRelationManager extends RelationManager
{
    protected static string $relationship = 'auditLogs';

    protected static ?string $title = 'Riwayat Perubahan';

    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->default('Sistem')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Kejadian')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created'        => 'Dibuat',
                        'status_changed' => 'Status Berubah',
                        'assigned'       => 'Ditugaskan',
                        'resolved'       => 'Diselesaikan',
                        'closed'         => 'Ditutup',
                        'updated'        => 'Diperbarui',
                        default          => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'created'        => 'info',
                        'status_changed' => 'warning',
                        'assigned'       => 'primary',
                        'resolved'       => 'success',
                        'closed'         => 'gray',
                        'updated'        => 'gray',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false)
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
