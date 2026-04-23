<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'modules', 'name' => 'enable_vehicle_booking', 'payload' => 'true', 'locked' => false],
            ['group' => 'modules', 'name' => 'enable_helpdesk_tickets', 'payload' => 'true', 'locked' => false],
            ['group' => 'modules', 'name' => 'enable_inventory', 'payload' => 'true', 'locked' => false],
            ['group' => 'modules', 'name' => 'enable_blog', 'payload' => 'true', 'locked' => false],
            ['group' => 'modules', 'name' => 'enable_user_management', 'payload' => 'true', 'locked' => false],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['group' => $setting['group'], 'name' => $setting['name']],
                $setting
            );
        }
    }
}
