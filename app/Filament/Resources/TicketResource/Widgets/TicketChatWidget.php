<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\Ticket;
use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class TicketChatWidget extends Widget
{
    protected static string $view = 'filament.resources.ticket-resource.widgets.ticket-chat-widget';

    protected int | string | array $columnSpan = 'full';

    public ?Model $record = null;

    public string $message = '';

    public bool $isInternalNote = false;

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|min:3',
        ], [
            'message.required' => 'Pesan tidak boleh kosong',
            'message.min' => 'Pesan minimal 3 karakter',
        ]);

        $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'Admin']);

        $this->record->responses()->create([
            'user_id' => auth()->id(),
            'message' => nl2br(e($this->message)),
            'is_internal_note' => $isAdmin && $this->isInternalNote,
        ]);

        // Set first_responded_at jika admin merespons untuk pertama kali (bukan catatan internal)
        if ($isAdmin && !$this->isInternalNote && !$this->record->first_responded_at) {
            $this->record->update(['first_responded_at' => now()]);
        }

        // Update status jika admin merespon dan tiket masih open
        if ($isAdmin && $this->record->status === 'open' && !$this->isInternalNote) {
            $this->record->update(['status' => 'in_progress']);
        }

        $this->message = '';
        $this->isInternalNote = false;

        Notification::make()
            ->title('Pesan terkirim')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $isAdmin = auth()->user()->hasAnyRole(['super_admin', 'Admin']);

        $responses = $this->record->responses()
            ->with('user')
            ->when(!$isAdmin, function ($query) {
                $query->where('is_internal_note', false);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return [
            'responses' => $responses,
            'ticket' => $this->record,
            'isAdmin' => $isAdmin,
        ];
    }
}
