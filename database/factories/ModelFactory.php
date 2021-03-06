<?php

use App\MasterData\Address;
use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Tasting\Commission;
use App\Tasting\Taster;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use App\Wine;
use App\WineQuality;
use Faker\Generator as FakeData;

$factory->define(User::class,
    function () {
        return [
        'username' => str_random(10),
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'admin' => false,
    ];
    });

$factory->state(User::class, 'admin',
    function () {
        return [
        'admin' => true,
    ];
    });

$factory->define(Association::class,
    function () {
        return [
        'id' => rand(10000, 99000),
        'name' => str_random(10),
        'wuser_username' => null,
    ];
    });

$factory->define(Applicant::class,
    function (FakeData $faker) {
        return [
        'id' => rand(10000, 999999),
        'association_id' => function () {
            return factory(Association::class)->create()->id;
        },
        'wuser_username' => null,
        'address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'label' => str_random(10),
        'title' => 'Dr.',
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'phone' => substr($faker->phoneNumber, 0, 20),
        'fax' => substr($faker->phoneNumber, 0, 20),
        'mobile' => substr($faker->phoneNumber, 0, 20),
        'email' => $faker->email,
        'web' => substr($faker->url, 0, 50),
    ];
    });

$factory->define(Address::class,
    function (FakeData $faker) {
        return [
        'street' => $faker->streetAddress,
        'nr' => $faker->numberBetween(1, 300),
        'zipcode' => $faker->numberBetween(1000, 9999),
        'city' => $faker->city,
    ];
    });

$factory->define(Competition::class,
    function () {
        return [
        'label' => str_random(10),
        'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
        'wuser_username' => null,
    ];
    });

$factory->define(Wine::class,
    function () {
        return [
        'nr' => rand(1, 1000),
        'label' => str_random(10),
        'vintage' => rand(2005, 2020),
        'alcohol' => rand(1, 200) / 10,
        'acidity' => rand(1, 200) / 10,
        'sugar' => rand(1, 300) / 10,
        'approvalnr' => str_random(15),
        'winesort_id' => function () {
            return factory(WineSort::class)->create()->id;
        },
        'competition_id' => function () {
            return factory(Competition::class)->create()->id;
        },
        'applicant_id' => function () {
            return factory(Applicant::class)->create()->id;
        },
        'winequality_id' => rand(1, 10), // Hard-coded, but should exist in DB
    ];
    });

$factory->define(WineQuality::class,
    function () {
        return [
        'id' => rand(100, 1000),
        'label' => str_random(10),
        'abbr' => strtoupper(str_random(3)),
    ];
    });

$factory->define(WineSort::class, function () {
    return [
        'order' => rand(1, 50000),
        'name' => str_random(10),
        'quality_allowed' => '[]',
    ];
});

$factory->define(TastingNumber::class,
    function () {
        return [
        'tastingstage_id' => rand(1, 2),
        'wine_id' => function () {
            return factory(Wine::class)->create()->id;
        },
        'nr' => rand(1, 5000),
    ];
    });

$factory->define(TastingSession::class, function () {
    return [
        'competition_id' => function () {
            return factory(Competition::class)->create()->id;
        },
        'tastingstage_id' => rand(1, 2),
        'nr' => rand(1, 100),
        'locked' => false,
    ];
});

$factory->define(Commission::class, function () {
    return [
        'tastingsession_id' => function () {
            return factory(TastingSession::class)->create()->id;
        },
        'side' => 'a',
    ];
});

$factory->define(Taster::class, function () {
    return [
        'commission_id' => function () {
            return factory(Commission::class)->create()->id;
        },
        'nr' => rand(1, 1000),
        'name' => str_random(10),
        'active' => true,
    ];
});
