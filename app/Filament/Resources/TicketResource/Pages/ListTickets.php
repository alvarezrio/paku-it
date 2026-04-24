<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Rekam Tiket')
                ->icon('heroicon-o-plus')
                ->url(TicketResource::getUrl('select-service')),
        ];
    }


    public function getTabs(): array
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'Admin']);

        // Helper untuk membuat query dengan filter user
        $getCount = function (string $status) use ($user, $isAdmin) {
            $query = \App\Models\Ticket::where('status', $status);
            if (!$isAdmin) {
                $query->where('user_id', $user->id);
            }
            return $query->count();
        };

        return [
            'all' => Tab::make('Semua'),
            'open' => Tab::make('Dibuka')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'open'))
                ->badge(fn () => $getCount('open'))
                ->badgeColor('danger'),
            'in_progress' => Tab::make('Diproses')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress'))
                ->badge(fn () => $getCount('in_progress'))
                ->badgeColor('warning'),
            'resolved' => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'resolved'))
                ->badge(fn () => $getCount('resolved'))
                ->badgeColor('success'),
            'closed' => Tab::make('Ditutup')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed')),
        ];
    }
}
