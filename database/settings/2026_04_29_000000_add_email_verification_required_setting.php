<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Default false: user yang belum verifikasi email tetap bisa login
        $this->migrator->add('KaidoSetting.email_verification_required', false);
    }
};
