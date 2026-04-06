<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       //admin
       user::create([
        'name' => 'Admin Utama',
        'email' => 'admin@app.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
       ]);

       // petugas
       user::create([
        'name' => 'petugas Lab',
        'email' => 'petugas@app.com',
        'password' => bcrypt('password'),
        'role' => 'petugas',
       ]);

       // peminjam
       user::create([
        'name' => 'siswa 1',
        'email' => 'siswa@app.com',
        'password' => bcrypt('password'),
        'role' => 'peminjam',
       ]);
    }
}
