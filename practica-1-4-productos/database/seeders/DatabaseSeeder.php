<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Electrónica', 'Hogar', 'Deportes', 'Oficina', 'Ropa'] as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'empleado@demo.com'],
            [
                'name' => 'Empleado Demo',
                'password' => Hash::make('password123'),
                'role' => 'empleado',
            ]
        );
    }
}