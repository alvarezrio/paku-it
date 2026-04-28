<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SlaSettings extends Settings
{
    public int $critical_hours;
    public int $high_hours;
    public int $medium_hours;
    public int $low_hours;

    public static function group(): string
    {
        return 'sla';
    }
}
