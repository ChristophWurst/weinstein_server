<?php

use App\MasterData\Address;
use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
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

$factory->defineAs(User::class, 'admin',
	function () {
	return [
		'username' => str_random(10),
		'password' => bcrypt(str_random(10)),
		'remember_token' => str_random(10),
		'admin' => true,
	];
});

$factory->define(Association::class,
	function() {
	return [
		'id' => rand(10000, 99000),
		'name' => str_random(10),
		'wuser_username' => null,
	];
});

$factory->define(Applicant::class,
	function(FakeData $faker) {
	return [
		'id' => rand(10000, 999999),
		'association_id' => null, // Must be overriden, otherwise this fails due to referential integrity constraints
		'wuser_username' => null,
		'address_id' => null, // Will fail, too
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
	function(FakeData $faker) {
	return [
		'street' => $faker->streetAddress,
		'nr' => $faker->numberBetween(1, 300),
		'zipcode' => $faker->numberBetween(1000, 9999),
		'city' => $faker->city,
	];
});

$factory->define(Competition::class,
	function() {
	return [
		'label' => str_random(10),
		'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		'wuser_username' => null,
	];
});
