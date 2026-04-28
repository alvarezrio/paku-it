<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('responsible_section')->nullable()->after('location');
        });

        $devices = DB::table('devices')->select('id', 'location')->whereNotNull('location')->get();

        foreach ($devices as $device) {
            $location = trim($device->location);
            if ($location === '') {
                continue;
            }

            $floor = $location;
            $section = null;

            foreach ([' - ', ' – ', ', '] as $separator) {
                if (str_contains($location, $separator)) {
                    [$floor, $section] = array_pad(explode($separator, $location, 2), 2, null);
                    break;
                }
            }

            DB::table('devices')->where('id', $device->id)->update([
                'location' => trim($floor),
                'responsible_section' => $section ? trim($section) : null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('responsible_section');
        });
    }
};
