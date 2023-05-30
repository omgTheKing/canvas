<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */

use Canvas\Canvas;
use Illuminate\Support\Str;

$factory->define(\Canvas\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'username' => Str::slug($faker->userName),
        'password' => bcrypt($faker->password),
        'summary' => $faker->sentence,
        'avatar' => Canvas::gravatar($faker->email),
        'dark_mode' => $faker->numberBetween(0, 1),
        'digest' => $faker->numberBetween(0, 1),
        'locale' => $faker->locale,
        'role' => $faker->numberBetween(1, 3),
    ];
});
