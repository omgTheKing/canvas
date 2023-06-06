<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Canvas\Models\Topic::class, function (Faker\Generator $faker) {
    $word = $faker->word;
    return [
        'slug' => \Str::slug($word),
        'name' => $word,
        'blogger_id' => function () {
            return factory(\Canvas\Models\User::class)->create()->id;
        },
    ];
});
