<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ModuleSettings extends Settings
{
    public bool $enable_vehicle_booking;
    public bool $enable_helpdesk_tickets;
    public bool $enable_inventory;
    public bool $enable_blog;
    public bool $enable_user_management;

    public static function group(): string
    {
        return 'modules';
    }
}
