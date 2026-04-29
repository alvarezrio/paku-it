<?php

namespace App\Models;

use App\Observers\TicketObserver;
use App\Settings\SlaSettings;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'resolved_at'        => 'datetime',
        'closed_at'          => 'datetime',
        'first_responded_at' => 'datetime',
        'sla_due_at'         => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }

            $sla = app(SlaSettings::class);
            $ticket->sla_due_at = now()->addHours(
                match($ticket->priority) {
                    'critical' => $sla->critical_hours,
                    'high'     => $sla->high_hours,
                    'medium'   => $sla->medium_hours,
                    default    => $sla->low_hours,
                }
            );
        });
    }

    public static function generateTicketNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'TKT';
            $date = now()->format('Ymd');

            // Use lockForUpdate to prevent race conditions
            $lastTicket = self::whereDate('created_at', today())
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastTicket ? (int) substr($lastTicket->ticket_number, -4) + 1 : 1;

            return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(TicketAuditLog::class)->orderByDesc('created_at');
    }

    // Accessors
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'danger',
            'in_progress' => 'warning',
            'waiting_for_user' => 'info',
            'resolved' => 'success',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'incident_management' => 'Manajemen Insiden',
            'service_request'     => 'Permintaan Layanan',
            'user_support'        => 'Dukungan Pengguna',
            'access_management'   => 'Manajemen Akses',
            'asset_management'    => 'Manajemen Aset',
            'change_management'   => 'Manajemen Perubahan',
            'network_support'     => 'Dukungan Jaringan',
            'security_support'    => 'Dukungan Keamanan',
            'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
            default => $this->category,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => $this->priority,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Dibuka',
            'in_progress' => 'Diproses',
            'waiting_for_user' => 'Menunggu User',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            default => $this->status,
        };
    }

    public function isSlaOverdue(): bool
    {
        return $this->sla_due_at !== null
            && $this->sla_due_at->isPast()
            && in_array($this->status, ['open', 'in_progress', 'waiting_for_user']);
    }

    // Helper methods
    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'waiting_for_user']);
    }

    public function canBeEdited(): bool
    {
        return $this->status !== 'closed';
    }

    public function markAsResolved(?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_notes' => $notes,
            'resolved_at' => now(),
        ]);
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            // Jika masih ada penanggung jawab, langsung masuk in_progress
            // Jika tidak ada, kembali ke open agar bisa di-assign ulang
            'status'      => $this->assigned_to ? 'in_progress' : 'open',
            'resolved_at' => null,
            'closed_at'   => null,
        ]);
    }
}
