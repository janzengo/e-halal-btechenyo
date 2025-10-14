<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update Head account
        Admin::updateOrCreate(
            ['email' => 'ehalal.btechenyo@gmail.com'],
            [
                'username' => 'head',
                'password' => Hash::make('password'),
                'firstname' => 'Reanne',
                'lastname' => 'Dela Cruz',
                'photo' => 'default-head.jpg',
                'created_on' => now()->toDateString(),
                'role' => 'head',
                'gender' => 'Male',
            ]
        );

        // Create or update Officer account
        Admin::updateOrCreate(
            ['email' => 'janneiljanzen.go@gmail.com'],
            [
                'username' => 'janzengo',
                'password' => Hash::make('password'),
                'firstname' => 'Janneil Janzen',
                'lastname' => 'Go',
                'photo' => 'default-officer.jpg',
                'created_on' => now()->toDateString(),
                'role' => 'officer',
                'gender' => 'Male',
            ]
        );

        $this->command->info('Admin accounts created successfully!');
        $this->command->info('Head account: head / ehalal.btechenyo@gmail.com / password');
        $this->command->info('Officer account: janzengo / janneiljanzen.go@gmail.com / password');
    }
}
