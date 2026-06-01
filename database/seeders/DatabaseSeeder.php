<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        Role::findOrCreate('Super Admin');
        Role::findOrCreate('Owner');
        Role::findOrCreate('Manager');

        $user = User::firstOrCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->syncRoles(['Owner']);
    }
}
