<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Canvas\Models\Topic::class, function (Faker\Generator $faker) {
    return [
        'slug' => $faker->slug,
        'name' => $faker->word,
        'user_id' => function () {
            return factory(\Canvas\Models\User::class)->create()->id;
        },
    ];
});
