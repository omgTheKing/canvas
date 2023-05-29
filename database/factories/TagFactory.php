<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Canvas\Models\Tag::class, function (Faker\Generator $faker) {
    return [
        'slug' => $faker->slug,
        'name' => $faker->word,
        'blogger_id' => function () {
            return factory(\Canvas\Models\User::class)->create()->id;
        },
    ];
});
