<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ThemeColorPage extends Page
{
    protected static string $view = 'filament.pages.theme-color';
    protected static ?string $title = 'Warna Tema';
    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $slug = 'theme-color';
    protected static bool $shouldRegisterNavigation = false;

    public string $selectedColor = 'amber';
    public string $selectedGray  = 'slate';
    public int    $selectedLevel = 1;

    public function mount(): void
    {
        $user = auth()->user();
        $this->selectedColor = $user->theme_color      ?? 'amber';
        $this->selectedGray  = $user->theme_gray       ?? 'slate';
        $this->selectedLevel = (int) ($user->theme_gray_level ?? 1);
    }

    public function selectColor(string $color): void
    {
        $this->selectedColor = $color;
        auth()->user()->update(['theme_color' => $color]);
        Notification::make()->title('Warna aksen diperbarui')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function selectGray(string $gray): void
    {
        $this->selectedGray = $gray;
        auth()->user()->update(['theme_gray' => $gray]);
        Notification::make()->title('Tone dasar diperbarui')->success()->send();
        $this->redirect(static::getUrl());
    }

    public function selectLevel(int $level): void
    {
        $this->selectedLevel = $level;
        auth()->user()->update(['theme_gray_level' => $level]);
        Notification::make()->title('Intensitas diperbarui')->success()->send();
        $this->redirect(static::getUrl());
    }

    public static function getPrimaryColors(): array
    {
        return [
            'amber'  => ['label' => 'Amber',  'hex' => '#f59e0b'],
            'blue'   => ['label' => 'Biru',   'hex' => '#3b82f6'],
            'indigo' => ['label' => 'Indigo', 'hex' => '#6366f1'],
            'green'  => ['label' => 'Hijau',  'hex' => '#22c55e'],
            'red'    => ['label' => 'Merah',  'hex' => '#ef4444'],
            'violet' => ['label' => 'Violet', 'hex' => '#8b5cf6'],
            'orange' => ['label' => 'Oranye', 'hex' => '#f97316'],
            'teal'   => ['label' => 'Teal',   'hex' => '#14b8a6'],
            'pink'   => ['label' => 'Pink',   'hex' => '#ec4899'],
            'rose'   => ['label' => 'Rose',   'hex' => '#f43f5e'],
            'cyan'   => ['label' => 'Cyan',   'hex' => '#06b6d4'],
        ];
    }

    public static function getGrayTones(): array
    {
        // shades[0..3] = preview untuk level 1-4 (Terang → Tajam)
        return [
            'slate'   => ['label' => 'Slate',   'hex' => '#64748b', 'shades' => ['#cbd5e1', '#64748b', '#334155', '#0f172a']],
            'blue'    => ['label' => 'Biru',    'hex' => '#3b82f6', 'shades' => ['#93c5fd', '#3b82f6', '#1d4ed8', '#1e3a8a']],
            'indigo'  => ['label' => 'Indigo',  'hex' => '#6366f1', 'shades' => ['#a5b4fc', '#6366f1', '#4338ca', '#312e81']],
            'violet'  => ['label' => 'Violet',  'hex' => '#8b5cf6', 'shades' => ['#c4b5fd', '#8b5cf6', '#6d28d9', '#4c1d95']],
            'emerald' => ['label' => 'Hijau',   'hex' => '#10b981', 'shades' => ['#6ee7b7', '#10b981', '#047857', '#064e3b']],
            'teal'    => ['label' => 'Teal',    'hex' => '#14b8a6', 'shades' => ['#5eead4', '#14b8a6', '#0f766e', '#134e4a']],
            'rose'    => ['label' => 'Rose',    'hex' => '#f43f5e', 'shades' => ['#fda4af', '#f43f5e', '#be123c', '#881337']],
            'amber'   => ['label' => 'Amber',   'hex' => '#f59e0b', 'shades' => ['#fcd34d', '#f59e0b', '#b45309', '#78350f']],
            'orange'  => ['label' => 'Oranye',  'hex' => '#f97316', 'shades' => ['#fdba74', '#f97316', '#c2410c', '#7c2d12']],
            'pink'    => ['label' => 'Pink',    'hex' => '#ec4899', 'shades' => ['#f9a8d4', '#ec4899', '#be185d', '#831843']],
        ];
    }

    public static function getIntensityLevels(): array
    {
        return [
            1 => 'Terang',
            2 => 'Normal',
            3 => 'Gelap',
            4 => 'Tajam',
        ];
    }
}
