<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(\Canvas\Models\Post::class, function (Faker\Generator $faker) {
    return [
        'uuid' => $faker->uuid,
        'slug' => $faker->slug,
        'title' => $faker->word,
        'summary' => $faker->sentence,
        'body' => $faker->realText(),
        'published_at' => today()->toDateTimeString(),
        'featured_image' => null,
        'featured_image_caption' => $faker->sentence,
        'blogger_id' => function () {
            return factory(\Canvas\Models\User::class)->create()->id;
        },
        'view_count' => rand(1000, 9999),
        'meta' => [
            'title' => $faker->sentence,
            'description' => $faker->sentence,
            'canonical_link' => $faker->sentence,
        ],
    ];
});
