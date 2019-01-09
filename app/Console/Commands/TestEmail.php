<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Mail\Mailer;

class TestEmail extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'email:test {email}';

	/**
	 * @var string
	 */
	protected $description = 'Test the email configuration';

	/**
	 * @var Mailer
	 */
	private $mailer;

	/**
	 * @return void
	 */
	public function __construct(Mailer $mailer)
	{
		parent::__construct();
		$this->mailer = $mailer;
	}

	/**
	 * @return mixed
	 */
	public function handle()
	{
		$this->mailer->to($this->argument('email'))
			->send(new \App\Mail\TestEmail());
	}

}
