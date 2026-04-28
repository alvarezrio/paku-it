<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sla.critical_hours', 2);
        $this->migrator->add('sla.high_hours', 8);
        $this->migrator->add('sla.medium_hours', 24);
        $this->migrator->add('sla.low_hours', 72);
    }
};
