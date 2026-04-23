<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Settings\ModuleSettings;

class WelcomeMessageWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-message-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        // Only show this widget if the helpdesk module is enabled AND
        // if the user is a 'Member' OR if they are not an Admin/Super_Admin
        // (i.e., if they are a user who would *not* see the TicketStatsWidget)
        if (app(ModuleSettings::class)->enable_helpdesk_tickets) {
            // Check if the user is a 'Member'
            if ($user && $user->hasRole('Member')) {
                return true;
            }
            // Alternatively, show this to any user who doesn't see the TicketStatsWidget
            // This is a broader approach, assuming anyone not seeing ticket stats
            // should see a welcome message.
            if ($user && !$user->hasAnyRole(['super_admin', 'Admin'])) {
                return true;
            }
        }

        return false;
    }
}
