<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Role::findOrCreate('Super Admin');
        Role::findOrCreate('Owner');
        Role::findOrCreate('Manager');

        $admin = User::query()->updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@gpdistro.test')],
            [
                'name' => env('SEED_ADMIN_NAME', 'GPDISTRO Admin'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'change-me-now')),
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles(['Owner']);

        $this->call(DemoDataSeeder::class);
    }
}
