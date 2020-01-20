<?php

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        AdminUser::truncate();
        AdminUser::create([
            'username' => env('APP_NAME', 'fmock'),
            'password' => bcrypt('fmock'),
            'name' => 'Administrator',
        ]);
    }
}
