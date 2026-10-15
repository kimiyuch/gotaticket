<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public const CREDENTIALS_FILE = __DIR__.'/test-users.local.json';

    /**
     * Seed local test accounts from the git-ignored credentials file, if present.
     */
    public function run(): void
    {
        if (! file_exists(self::CREDENTIALS_FILE)) {
            return;
        }

        /** @var list<array{name: string, email: string, password: string}> $users */
        $users = json_decode(file_get_contents(self::CREDENTIALS_FILE), true, flags: JSON_THROW_ON_ERROR);

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], [
                'name' => $user['name'],
                'password' => $user['password'],
            ])->forceFill(['is_admin' => true, 'email_verified_at' => now()])->save();
        }
    }
}
