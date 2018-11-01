<?php

namespace App;


use App\MasterData\Association;
use App\MasterData\Competition;
use Illuminate\Database\Eloquent\Model;

class WinesChosenSignedOff extends Model
{
	/**
	 * @var string
	 */
	protected $table = 'wines_chosen_signed_off';

	/**
	 * @return Relation
	 */
	public function association()
	{
		return $this->belongsTo(Association::class);
	}

	/**
	 * @return Relation
	 */
	public function competition()
	{
		return $this->belongsTo(Competition::class);
	}

}