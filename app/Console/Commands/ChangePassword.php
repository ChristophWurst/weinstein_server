<?php

namespace App\Console\Commands;

use App\Contracts\MasterDataStore;
use Illuminate\Console\Command;

class ChangePassword extends Command
{
	/** @var MasterDataStore */
	private $store;

	protected $signature = 'user:change-password {username} {password}';

	protected $description = 'Update a user\'s password';

	public function __construct(MasterDataStore $store)
	{
		parent::__construct();
		$this->store = $store;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$username = $this->argument('username');
		$password = $this->argument('password');

		$user = $this->store->getUser($username);
		if (is_null($username)) {
			$this->warn("User does not exist");
			return;
		}

		$this->store->updateUser($user, [
			'username' => $username,
			'password' => $password,
		]);
	}
}
