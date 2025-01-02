<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Tag;
use App\Models\ToDo;
use Illuminate\Database\Seeder;

class ToDoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first admin
        $admin = Admin::first(); 

        if ($admin) {
            // Seed some example ToDo items for the first admin
            $todo1 = ToDo::create([
                'title' => 'Finish Laravel Project',
                'description' => 'Complete the features and tests for the project.',
                'is_completed' => false,
                'created_by' => $admin->id,
            ]);

            $todo2 = ToDo::create([
                'title' => 'Write Documentation',
                'description' => 'Write API documentation for the project.',
                'is_completed' => false,
                'created_by' => $admin->id,
            ]);
            
            // Get all tag IDs
            $tagIds = Tag::pluck('id')->toArray();

            // If there are tags, attach them to the todos
           if (!empty($tagIds)) {
                $pivotData = [];
                foreach ($tagIds as $tagId) {
                    $pivotData[$tagId] = [
                        'created_by' => $admin->id,
                        'updated_by' => $admin->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $todo1->tags()->attach($pivotData);
                $todo2->tags()->attach($pivotData);
            }
        }
    }
}
