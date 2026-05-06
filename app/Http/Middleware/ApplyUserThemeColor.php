<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;

class ApplyUserThemeColor
{
    public function handle(Request $request, Closure $next)
    {
        if ($user = auth()->user()) {
            $level = (int) ($user->theme_gray_level ?? 1);
            FilamentColor::register([
                'primary' => self::resolvePrimary($user->theme_color ?? 'amber'),
                'gray'    => self::resolveGray($user->theme_gray ?? 'slate', $level),
            ]);
        }

        return $next($request);
    }

    public static function resolvePrimary(string $name): array
    {
        return match ($name) {
            'blue'   => Color::Blue,
            'indigo' => Color::Indigo,
            'green'  => Color::Green,
            'red'    => Color::Red,
            'violet' => Color::Violet,
            'orange' => Color::Orange,
            'teal'   => Color::Teal,
            'pink'   => Color::Pink,
            'rose'   => Color::Rose,
            'cyan'   => Color::Cyan,
            default  => Color::Amber,
        };
    }

    public static function resolveGray(string $name, int $level = 1): array
    {
        $base = match ($name) {
            'blue'    => Color::Blue,
            'indigo'  => Color::Indigo,
            'violet'  => Color::Violet,
            'emerald' => Color::Emerald,
            'teal'    => Color::Teal,
            'rose'    => Color::Rose,
            'amber'   => Color::Amber,
            'orange'  => Color::Orange,
            'pink'    => Color::Pink,
            default   => Color::Slate,
        };

        // Level 1 = normal, level 2-4 = progressively darker (shift shades)
        $shift = match ($level) {
            2 => 2,
            3 => 4,
            4 => 6,
            default => 0,
        };

        return $shift > 0 ? self::shiftPalette($base, $shift) : $base;
    }

    // Geser seluruh palette ke shade yang lebih gelap
    // Contoh shift=2: shade-50 pakai nilai shade-200, shade-100 pakai nilai shade-300, dst.
    private static function shiftPalette(array $palette, int $shift): array
    {
        $keys   = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
        $result = [];

        foreach ($keys as $i => $key) {
            $sourceIndex = min($i + $shift, count($keys) - 1);
            $result[$key] = $palette[$keys[$sourceIndex]];
        }

        return $result;
    }
}
