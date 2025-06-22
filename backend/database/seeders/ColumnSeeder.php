<?php

namespace Database\Seeders;

use App\Models\Column;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColumnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $columns = [
            'Backlog',
            'Up Next',
            'In Progress',
            'Done'
        ];

        foreach ($columns as $column) {
            Column::create(['name' => $column]);
        }
    }
}
