<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'              => 'Admin User',
            'email'             => 'admin@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        // Dedicated test customer
        User::create([
            'name'              => 'Test Customer',
            'email'             => 'customer@example.com',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);

        // Real-looking customers
        $customers = [
            ['name' => 'James Anderson',   'email' => 'james.anderson@gmail.com'],
            ['name' => 'Sarah Williams',   'email' => 'sarah.williams@yahoo.com'],
            ['name' => 'Michael Johnson',  'email' => 'michael.j@outlook.com'],
            ['name' => 'Emily Davis',      'email' => 'emily.davis@gmail.com'],
            ['name' => 'Robert Martinez',  'email' => 'rmartinez@hotmail.com'],
            ['name' => 'Jessica Taylor',   'email' => 'jessica.taylor@gmail.com'],
            ['name' => 'David Thompson',   'email' => 'david.thompson@icloud.com'],
            ['name' => 'Ashley Garcia',    'email' => 'ashley.garcia@yahoo.com'],
            ['name' => 'Christopher Lee',  'email' => 'chris.lee@gmail.com'],
            ['name' => 'Amanda Wilson',    'email' => 'amanda.wilson@outlook.com'],
        ];

        foreach ($customers as $customer) {
            User::create([
                'name'              => $customer['name'],
                'email'             => $customer['email'],
                'password'          => Hash::make('password'),
                'role'              => 'customer',
                'email_verified_at' => now()->subDays(rand(1, 365)),
            ]);
        }
    }
}