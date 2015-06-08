<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $creds = Config::get('madison.seeder');

        factory('MXAbierto\Participa\Models\User')->create([
            'email'    => $creds['admin_email'],
            'password' => $creds['admin_password'],
            'fname'    => $creds['admin_fname'],
            'lname'    => $creds['admin_lname'],
        ]);

        factory('MXAbierto\Participa\Models\User')->create([
            'email'    => $creds['user_email'],
            'password' => $creds['user_password'],
            'fname'    => $creds['user_fname'],
            'lname'    => $creds['user_lname'],
        ]);

        factory('MXAbierto\Participa\Models\User')->times(3)->create();
    }
}
