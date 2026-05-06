<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $appointments = [
            ['name' => 'Barber Shop', 'slug' => 'barber-shop', 'icon' => 'heroicon-o-scissors'],
            ['name' => 'Hair Salon', 'slug' => 'hair-salon', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Med Spa', 'slug' => 'med-spa', 'icon' => 'heroicon-o-heart'],
            ['name' => 'Nail Salon', 'slug' => 'nail-salon', 'icon' => 'heroicon-o-hand-raised'],
            ['name' => 'Esthetician', 'slug' => 'esthetician', 'icon' => 'heroicon-o-face-smile'],
            ['name' => 'Massage Therapy', 'slug' => 'massage-therapy', 'icon' => 'heroicon-o-hand-thumb-up'],
            ['name' => 'Tattoo Studio', 'slug' => 'tattoo-studio', 'icon' => 'heroicon-o-pencil'],
            ['name' => 'Lash & Brow', 'slug' => 'lash-and-brow', 'icon' => 'heroicon-o-eye'],
            ['name' => 'Personal Training', 'slug' => 'personal-training', 'icon' => 'heroicon-o-bolt'],
            ['name' => 'Wellness & Therapy', 'slug' => 'wellness-and-therapy', 'icon' => 'heroicon-o-heart'],
        ];

        $skilledTrades = [
            ['name' => 'Plumbing', 'slug' => 'plumbing', 'icon' => 'heroicon-o-wrench'],
            ['name' => 'Electrical', 'slug' => 'electrical', 'icon' => 'heroicon-o-bolt'],
            ['name' => 'HVAC', 'slug' => 'hvac', 'icon' => 'heroicon-o-fire'],
            ['name' => 'Handyman', 'slug' => 'handyman', 'icon' => 'heroicon-o-wrench-screwdriver'],
            ['name' => 'Cleaning Services', 'slug' => 'cleaning-services', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Landscaping', 'slug' => 'landscaping', 'icon' => 'heroicon-o-globe-alt'],
            ['name' => 'Painting', 'slug' => 'painting', 'icon' => 'heroicon-o-paint-brush'],
            ['name' => 'Roofing', 'slug' => 'roofing', 'icon' => 'heroicon-o-home'],
            ['name' => 'Pest Control', 'slug' => 'pest-control', 'icon' => 'heroicon-o-bug-ant'],
            ['name' => 'Carpet Cleaning', 'slug' => 'carpet-cleaning', 'icon' => 'heroicon-o-square-3-stack-3d'],
        ];

        $rows = [];
        $sortOrder = 0;

        foreach ($appointments as $category) {
            $rows[] = array_merge($category, [
                'parent_id' => null,
                'vertical' => 'appointments',
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $sortOrder = 0;

        foreach ($skilledTrades as $category) {
            $rows[] = array_merge($category, [
                'parent_id' => null,
                'vertical' => 'skilled_trades',
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('business_categories')->upsert(
            $rows,
            ['slug'],
            ['name', 'icon', 'vertical', 'sort_order', 'is_active', 'updated_at']
        );
    }
}
