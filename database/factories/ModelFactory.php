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
        'id' => random_int(10000, 99000),
        'name' => str_random(10),
        'wuser_username' => null,
    ];
    });

$factory->define(Applicant::class,
    function () {
        return [
        'id' => random_int(10000, 999999),
        'association_id' => function () {
            return factory(Association::class)->create()->id;
        },
        'wuser_username' => null,
        'address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'label' => str_random(10),
        'title' => 'Dr.',
        'firstname' => str_random(20),
        'lastname' => str_random(20),
        'phone' => random_int(10000, 100000000),
        'fax' => random_int(10000, 100000000),
        'mobile' => random_int(10000, 100000000),
        'email' => str_random(8) . '@' . str_random(5) . '.com',
        'web' => str_random(10) . '.com',
    ];
    });

$factory->define(Address::class,
    function () {
        return [
        'street' => str_random(20),
        'nr' => random_int(1, 300),
        'zipcode' => random_int(1000, 9999),
        'city' => str_random(10),
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
        'nr' => random_int(1, 1000),
        'label' => str_random(10),
        'vintage' => random_int(2005, 2020),
        'alcohol' => random_int(1, 200) / 10,
        'acidity' => random_int(1, 200) / 10,
        'sugar' => random_int(1, 300) / 10,
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
        'winequality_id' => random_int(1, 10), // Hard-coded, but should exist in DB
    ];
    });

$factory->define(WineQuality::class,
    function () {
        return [
        'id' => random_int(100, 1000),
        'label' => str_random(10),
        'abbr' => strtoupper(str_random(3)),
    ];
    });

$factory->define(WineSort::class, function () {
    return [
        'order' => random_int(1, 50000),
        'name' => str_random(10),
        'quality_allowed' => '[]',
    ];
});

$factory->define(TastingNumber::class,
    function () {
        return [
        'tastingstage_id' => random_int(1, 2),
        'wine_id' => function () {
            return factory(Wine::class)->create()->id;
        },
        'nr' => random_int(1, 5000),
    ];
    });

$factory->define(TastingSession::class, function () {
    return [
        'competition_id' => function () {
            return factory(Competition::class)->create()->id;
        },
        'tastingstage_id' => random_int(1, 2),
        'nr' => random_int(1, 100),
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
        'nr' => random_int(1, 1000),
        'name' => str_random(10),
        'active' => true,
    ];
});
