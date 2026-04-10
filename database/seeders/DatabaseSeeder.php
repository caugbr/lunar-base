<?php

namespace Database\Seeders;

// use App\Models\User;
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
        // User::factory(10)->create();

        $this->call([
            RolePermissionSeeder::class,
            TaxonomySeeder::class,
            AdminUserSeeder::class,
            // Adicione outros seeders aqui
        ]);

        $this->command->info('Database seeded com sucesso!');
        $this->command->info('Admin: caugbr@gmail.com');
    }
}
