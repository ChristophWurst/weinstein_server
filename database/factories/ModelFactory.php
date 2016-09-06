<?php

use App\MasterData\Association;
use App\MasterData\User;

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
