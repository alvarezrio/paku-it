<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketAuditLog;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Cache;

class TicketObserver
{
    /**
     * Clear navigation badge cache for all affected users.
     */
    protected function clearBadgeCache(Ticket $ticket): void
    {
        // Clear cache for the ticket owner
        Cache::forget("ticket_badge_{$ticket->user_id}_user");
        Cache::forget("ticket_badge_color_{$ticket->user_id}_user");

        // Clear cache for assigned admin if exists
        if ($ticket->assigned_to) {
            Cache::forget("ticket_badge_{$ticket->assigned_to}_admin");
            Cache::forget("ticket_badge_color_{$ticket->assigned_to}_admin");
        }

        // Clear cache for all admins (they see all tickets)
        $admins = User::role('super_admin')->pluck('id');
        foreach ($admins as $adminId) {
            Cache::forget("ticket_badge_{$adminId}_admin");
            Cache::forget("ticket_badge_color_{$adminId}_admin");
        }
    }

    /**
     * Write an audit log entry for the ticket.
     */
    protected function writeAuditLog(Ticket $ticket, string $event, string $description, array $oldValues = [], array $newValues = []): void
    {
        TicketAuditLog::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => auth()->id(),
            'event'       => $event,
            'description' => $description,
            'old_values'  => count($oldValues) ? $oldValues : null,
            'new_values'  => count($newValues) ? $newValues : null,
        ]);
    }

    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // Clear badge cache when new ticket is created
        $this->clearBadgeCache($ticket);

        // Catat audit log
        $this->writeAuditLog($ticket, 'created', "Tiket {$ticket->ticket_number} dibuat oleh pelapor.", [], [
            'subject'  => $ticket->subject,
            'category' => $ticket->category,
            'priority' => $ticket->priority,
        ]);

        // Notifikasi ke semua admin bahwa ada tiket baru
        $admins = User::role('super_admin')->where('id', '!=', $ticket->user_id)->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Tiket Baru: ' . $ticket->ticket_number)
                ->body($ticket->subject)
                ->icon('heroicon-o-ticket')
                ->iconColor('info')
                ->actions([
                    Action::make('view')
                        ->label('Lihat')
                        ->url(route('filament.admin.resources.tickets.view', $ticket))
                        ->markAsRead(),
                ])
                ->sendToDatabase($admin);
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Clear badge cache when ticket status or assignment changes
        if ($ticket->isDirty(['status', 'assigned_to'])) {
            $this->clearBadgeCache($ticket);
        }

        // Cek apakah status berubah
        if ($ticket->isDirty('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $newStatus = $ticket->status;

            $statusLabels = [
                'open' => 'Dibuka',
                'in_progress' => 'Diproses',
                'waiting_for_user' => 'Menunggu User',
                'resolved' => 'Selesai',
                'closed' => 'Ditutup',
            ];

            // Audit log untuk perubahan status
            $this->writeAuditLog(
                $ticket,
                'status_changed',
                "Status berubah dari \"{$statusLabels[$oldStatus]}\" menjadi \"{$statusLabels[$newStatus]}\".",
                ['status' => $oldStatus],
                ['status' => $newStatus]
            );

            $statusColors = [
                'open' => 'danger',
                'in_progress' => 'warning',
                'waiting_for_user' => 'info',
                'resolved' => 'success',
                'closed' => 'gray',
            ];

            // Notifikasi ke pelapor jika bukan dia yang mengubah
            $reporter = User::find($ticket->user_id);
            if ($reporter && $ticket->user_id !== auth()->id()) {
                Notification::make()
                    ->title('Status Tiket Diperbarui')
                    ->body("Tiket {$ticket->ticket_number} status berubah menjadi: {$statusLabels[$newStatus]}")
                    ->icon('heroicon-o-arrow-path')
                    ->iconColor($statusColors[$newStatus] ?? 'gray')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Tiket')
                            ->url(route('filament.admin.resources.tickets.view', $ticket))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($reporter);
            }

            // Notifikasi ke assigned admin jika ada dan bukan dia yang mengubah
            $assignedUser = $ticket->assigned_to ? User::find($ticket->assigned_to) : null;
            if ($assignedUser && $ticket->assigned_to !== auth()->id()) {
                Notification::make()
                    ->title('Status Tiket Diperbarui')
                    ->body("Tiket {$ticket->ticket_number} status berubah: {$statusLabels[$oldStatus]} → {$statusLabels[$newStatus]}")
                    ->icon('heroicon-o-arrow-path')
                    ->iconColor($statusColors[$newStatus] ?? 'gray')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Tiket')
                            ->url(route('filament.admin.resources.tickets.view', $ticket))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($assignedUser);
            }
        }

        // Cek apakah tiket di-assign ke seseorang
        if ($ticket->isDirty('assigned_to') && $ticket->assigned_to) {
            $newAssignedUser = User::find($ticket->assigned_to);

            // Audit log untuk assignment
            $oldAssigned = $ticket->getOriginal('assigned_to')
                ? (User::find($ticket->getOriginal('assigned_to'))?->name ?? 'Tidak diketahui')
                : 'Belum ditugaskan';
            $this->writeAuditLog(
                $ticket,
                'assigned',
                "Tiket ditugaskan ke {$newAssignedUser?->name}.",
                ['assigned_to' => $oldAssigned],
                ['assigned_to' => $newAssignedUser?->name]
            );

            // Jangan kirim notifikasi jika self-assign
            if ($newAssignedUser && $ticket->assigned_to !== auth()->id()) {
                Notification::make()
                    ->title('Tiket Ditugaskan ke Anda')
                    ->body("Anda ditugaskan untuk menangani tiket: {$ticket->ticket_number}")
                    ->icon('heroicon-o-user-plus')
                    ->iconColor('warning')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Tiket')
                            ->url(route('filament.admin.resources.tickets.view', $ticket))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($newAssignedUser);
            }

            // Notifikasi ke pelapor
            $ticketOwner = User::find($ticket->user_id);
            if ($ticketOwner && $newAssignedUser && $ticket->user_id !== auth()->id()) {
                Notification::make()
                    ->title('Tiket Anda Sedang Ditangani')
                    ->body("Tiket {$ticket->ticket_number} sedang ditangani oleh {$newAssignedUser->name}")
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->iconColor('success')
                    ->actions([
                        Action::make('view')
                            ->label('Lihat Tiket')
                            ->url(route('filament.admin.resources.tickets.view', $ticket))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($ticketOwner);
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        // Clear badge cache when ticket is deleted
        $this->clearBadgeCache($ticket);
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
