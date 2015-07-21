<?php

use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run()
    {
        $test_fname = 'Alice';
        $test_lname = 'Wonderland';
        $test_password = 'password';

        factory('MXAbierto\Participa\Models\User')->create([
            'email'    => 'test@opengovfoundation.org',
            'password' => $test_password,
            'fname'    => $test_fname,
            'lname'    => $test_lname,
            'token'    => '12345',
        ]);

        factory('MXAbierto\Participa\Models\User')->create([
            'email'    => 'test2@opengovfoundation.org',
            'password' => $test_password,
            'fname'    => $test_fname,
            'lname'    => $test_lname,
            'token'    => '12345',
        ]);
    }
}
