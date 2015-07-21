<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define('MXAbierto\Participa\Models\User', function ($faker) {
    return [
        'fname'          => $faker->name,
        'lname'          => $faker->lastName,
        'email'          => $faker->email,
        'password'       => str_random(10),
        'remember_token' => str_random(10),
    ];
});

$factory->define('MXAbierto\Participa\Models\Doc', function ($faker) {
    $title = $faker->sentence;

    return [
        'title' => $title,
        'slug'  => str_slug($title),
    ];
});

$factory->define('MXAbierto\Participa\Models\DocContent', function ($faker) {
    return [
        'content' => $faker->text,
    ];
});
