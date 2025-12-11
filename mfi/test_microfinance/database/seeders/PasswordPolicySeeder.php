<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasswordPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $policy = [
            'requireSpecialCharacter' => true,
            'length' => '8',
            'requireUppercase' => true,
            'requireNumeric' => true,
            'limiter' => 5,
            'passwordExpire' => 90, // Password expires after 90 days
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()];

        DB::table('password_policies')->updateOrInsert(
            ['status' => 'active'],
            $policy
        );
    }
}
