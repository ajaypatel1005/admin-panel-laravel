<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = Admin::first()->id ?? 1; // Get first admin's ID or default to 1

        $tags = ['Urgent', 'Important', 'Low Priority', 'Work', 'Personal'];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag,
                'created_by' => $adminId,
            ]);
        }
    }
}
