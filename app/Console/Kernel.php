<?php

namespace App\Console;

use App\Console\Commands\AddMissingApplicantUsers;
use App\Console\Commands\ChangePassword;
use App\Console\Commands\CreateUser;
use App\Console\Commands\TestEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		AddMissingApplicantUsers::class,
		ChangePassword::class,
		CreateUser::class,
		TestEmail::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule) {
		// $schedule->command('inspire')
		//          ->hourly();
	}

}
