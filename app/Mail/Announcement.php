<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Announcement extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @return void
	 */
	public function __construct(string $subject, string $text)
	{
		$this->subject = $subject;
		$this->text = $text;
	}

	/**
	 * @return $this
	 */
	public function build()
	{
		return $this->text('emails.announcement')
			->subject($this->subject)
			->with([
				'text' => $this->text,
			]);
	}

}
