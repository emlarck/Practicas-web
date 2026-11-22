<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gato.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'jugador1@gato.com'],
            [
                'name' => 'Jugador Uno',
                'password' => Hash::make('password123'),
                'role' => 'jugador',
            ]
        );

        User::firstOrCreate(
            ['email' => 'jugador2@gato.com'],
            [
                'name' => 'Jugador Dos',
                'password' => Hash::make('password123'),
                'role' => 'jugador',
            ]
        );
    }
}