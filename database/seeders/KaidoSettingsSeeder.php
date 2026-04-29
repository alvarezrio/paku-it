<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaidoSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // KaidoSetting — pengaturan umum situs
        $kaidoSettings = [
            ['name' => 'site_name',                  'payload' => '"ACI-421"'],
            ['name' => 'site_active',                'payload' => 'true'],
            ['name' => 'registration_enabled',       'payload' => 'false'],
            ['name' => 'login_enabled',              'payload' => 'true'],
            ['name' => 'password_reset_enabled',     'payload' => 'false'],
            ['name' => 'sso_enabled',                'payload' => 'false'],
            ['name' => 'email_verification_required','payload' => 'false'],
        ];

        foreach ($kaidoSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'KaidoSetting', 'name' => $setting['name']],
                ['group' => 'KaidoSetting', 'name' => $setting['name'], 'payload' => $setting['payload'], 'locked' => false]
            );
        }

        // ModuleSettings — toggle modul fitur
        $moduleSettings = [
            ['name' => 'enable_vehicle_booking',  'payload' => 'true'],
            ['name' => 'enable_helpdesk_tickets', 'payload' => 'true'],
            ['name' => 'enable_inventory',        'payload' => 'true'],
            ['name' => 'enable_blog',             'payload' => 'true'],
            ['name' => 'enable_user_management',  'payload' => 'true'],
        ];

        foreach ($moduleSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'modules', 'name' => $setting['name']],
                ['group' => 'modules', 'name' => $setting['name'], 'payload' => $setting['payload'], 'locked' => false]
            );
        }

        // SLA Settings — default SLA helpdesk (jam)
        $slaSettings = [
            ['name' => 'critical_hours', 'payload' => '4'],
            ['name' => 'high_hours',     'payload' => '8'],
            ['name' => 'medium_hours',   'payload' => '24'],
            ['name' => 'low_hours',      'payload' => '72'],
        ];

        foreach ($slaSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'sla', 'name' => $setting['name']],
                ['group' => 'sla', 'name' => $setting['name'], 'payload' => $setting['payload'], 'locked' => false]
            );
        }
    }
}
