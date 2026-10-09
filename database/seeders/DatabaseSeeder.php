<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds no user accounts on purpose: a seeded login with a known password
     * would be an admin backdoor. Create admins manually (see is_admin).
     */
    public function run(): void
    {
        //
    }
}
