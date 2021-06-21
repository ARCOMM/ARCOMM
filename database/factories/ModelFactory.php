<?php

namespace Database\Factories;

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

$factory->define(App\Models\JoinRequests\JoinRequest::class, function(Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'age' => $faker->numberBetween(15, 45),
        'location' => $faker->country,
        'email' => 'jlkingsley97@gmail.com',
        'steam' => $faker->url,
        'available' => $faker->boolean,
        'apex' => $faker->boolean,
        'groups' => $faker->boolean,
        'experience' => $faker->paragraph,
        'bio' => $faker->paragraphs(3, true),
        'source_id' => $faker->numberBetween(1, 10),
        'source_text' => '',
        'status_id' => $faker->numberBetween(1, 3)
    ];
});
