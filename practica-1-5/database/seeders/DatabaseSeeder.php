<?php

namespace Database\Seeders;

use App\Models\Computer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@lab.com'],
            [
                'name' => 'Administrador Lab',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'estudiante1@lab.com'],
            [
                'name' => 'Estudiante Uno',
                'password' => Hash::make('password123'),
                'role' => 'estudiante',
            ]
        );

        User::firstOrCreate(
            ['email' => 'estudiante2@lab.com'],
            [
                'name' => 'Estudiante Dos',
                'password' => Hash::make('password123'),
                'role' => 'estudiante',
            ]
        );

        $equipos = [
            ['code' => 'PC-01', 'name' => 'Equipo 01', 'specifications' => 'Ryzen 5, 16GB RAM, SSD 512GB', 'lab_room' => 'Lab 1', 'status' => 'disponible'],
            ['code' => 'PC-02', 'name' => 'Equipo 02', 'specifications' => 'Ryzen 5, 16GB RAM, SSD 512GB', 'lab_room' => 'Lab 1', 'status' => 'disponible'],
            ['code' => 'PC-03', 'name' => 'Equipo 03', 'specifications' => 'Core i7, 32GB RAM, SSD 1TB', 'lab_room' => 'Lab 1', 'status' => 'disponible'],
            ['code' => 'PC-04', 'name' => 'Equipo 04', 'specifications' => 'Core i5, 16GB RAM, SSD 512GB', 'lab_room' => 'Lab 2', 'status' => 'disponible'],
        ];

        foreach ($equipos as $eq) {
            Computer::firstOrCreate(['code' => $eq['code']], $eq);
        }
    }
}