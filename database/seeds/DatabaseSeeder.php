<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::table('chats')->insert([
            'id' => '192548341',
            'token' => 'd61a740891046656c5e3f39e183e16b7931c3830bc2134a9e67f5e00c291c187518a0e87b4b7de463ada7',
        ]);
    }
}
