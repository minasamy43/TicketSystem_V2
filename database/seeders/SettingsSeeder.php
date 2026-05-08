<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed the Active Preferences (Current state)
        $settings = [
            'site_name' => 'PurTK',
            'site_logo' => 'img/PurpleLogo.png',
            'primary_color' => '#a600ff',
            'sidebar_bg' => '#ffffff',
            'navbar_bg' => '#ffffff',
            'sidebar_text' => '#0d0c0c',
            'navbar_text' => '#6c7380',
            'site_name_color' => '#1a1a1a',
            'user_name_color' => '#6c7380',
            'sidebar_separator' => '#e6e9f4',
            'menu_title_color' => '#828795',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        // 2. Seed the 3 Custom Saved Designs
        $savedThemes = [
            [
                'id' => 'theme_69f9b09e695d6',
                'name' => 'Green Design',
                'colors' => [
                    'primary_color' => '#1db937',
                    'sidebar_bg' => '#ffffff',
                    'navbar_bg' => '#ffffff',
                    'sidebar_text' => '#45784b',
                    'navbar_text' => '#6c7380',
                    'site_name_color' => '#437044',
                    'user_name_color' => '#6c7380',
                    'sidebar_separator' => '#e6e9f4',
                    'menu_title_color' => '#828795',
                    'site_name' => 'GreenKey',
                    'site_logo' => 'img/GreenLogo.png',
                ],
                'created_at' => '2026-05-05 11:55:58',
            ],
            [
                'id' => 'theme_69f9b11006761',
                'name' => 'Blue design',
                'colors' => [
                    'primary_color' => '#006eff',
                    'sidebar_bg' => '#ffffff',
                    'navbar_bg' => '#ffffff',
                    'sidebar_text' => '#6c7380',
                    'navbar_text' => '#6c7380',
                    'site_name_color' => '#1a1a1a',
                    'user_name_color' => '#6c7380',
                    'sidebar_separator' => '#e6e9f4',
                    'menu_title_color' => '#828795',
                    'site_name' => 'Meta',
                    'site_logo' => 'img/BlueLogo.png',
                ],
                'created_at' => '2026-05-05 11:57:52',
            ],
            [
                'id' => 'theme_69f9b763f1847',
                'name' => 'Purple design',
                'colors' => [
                    'primary_color' => '#a600ff',
                    'sidebar_bg' => '#ffffff',
                    'navbar_bg' => '#ffffff',
                    'sidebar_text' => '#0d0c0c',
                    'navbar_text' => '#6c7380',
                    'site_name_color' => '#1a1a1a',
                    'user_name_color' => '#6c7380',
                    'sidebar_separator' => '#e6e9f4',
                    'menu_title_color' => '#828795',
                    'site_name' => 'PurTK',
                    'site_logo' => 'img/PurpleLogo.png',
                ],
                'created_at' => '2026-05-05 12:24:51',
            ],
            [
                'id' => 'theme_69f9b900a1234',
                'name' => 'Red Design',
                'colors' => [
                    'primary_color' => '#e53935',
                    'sidebar_bg' => '#ffffff',
                    'navbar_bg' => '#ffffff',
                    'sidebar_text' => '#3b1010',
                    'navbar_text' => '#6c7380',
                    'site_name_color' => '#030303ff',
                    'user_name_color' => '#6c7380',
                    'sidebar_separator' => '#fde8e8',
                    'menu_title_color' => '#c0736f',
                    'site_name' => 'RedTK',
                    'site_logo' => 'img/RedLogo.png',
                ],
                'created_at' => '2026-05-08 17:00:00',
            ],
        ];

        Setting::set('saved_themes', json_encode($savedThemes));
    }
}
