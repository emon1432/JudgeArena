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
        $this->call(CountriesTableSeeder::class);
        $this->call(InstitutesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(PlatformsTableSeeder::class);
        $this->call(PlatformProfilesTableSeeder::class);
        $this->call(SyncLogsTableSeeder::class);
        $this->call(ContestsTableSeeder::class);
        $this->call(ProblemsTableSeeder::class);
    }
}
