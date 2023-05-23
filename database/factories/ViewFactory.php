<?php

/* @var \Illuminate\Database\Eloquent\Factory $factory */

use Ramsey\Uuid\Uuid;

$factory->define(\Canvas\Models\View::class, function (Faker\Generator $faker) {
    return [
        'post_id' => Uuid::uuid4()->toString(),
        'ip' => $faker->ipv4,
        'agent' => $faker->userAgent,
        'referer' => $faker->url,
    ];
});
