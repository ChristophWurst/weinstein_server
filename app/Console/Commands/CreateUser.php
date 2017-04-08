<?php

namespace App\Console\Commands;

use App\Database\Repositories\UserRepository;
use Illuminate\Console\Command;

class CreateUser extends Command {

	/** @var UserRepository */
	private $userRepo;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'user:create {username} {password}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new user';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(UserRepository $userRepo) {
		parent::__construct();
		$this->userRepo = $userRepo;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$user = $this->userRepo->create([
			'username' => $this->argument('username'),
			'password' => $this->argument('password'),
		]);

		$this->info('User <' . $user->username . '> created successfully.');
	}

}
