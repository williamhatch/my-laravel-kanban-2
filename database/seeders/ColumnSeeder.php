<?php

namespace Database\Seeders;

use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ColumnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $columns = [
            'Backlog',
            'Up Next',
            'In Progress',
            'Done'
        ];

        foreach ($columns as $index => $name) {
            Column::create([
                'name' => $name,
                'order' => $index,
                'user_id' => $user->id,
            ]);
        }
    }
} 