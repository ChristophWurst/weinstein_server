<?php

namespace App\Console\Commands;

use App\Contracts\MasterDataStore;
use Illuminate\Console\Command;

class AddMissingApplicantUsers extends Command
{
    protected $signature = 'applicant:users-fix';

    protected $description = 'Add missing applicant users';
    /**
     * @var MasterDataStore
     */
    private $store;

    /**
     * @return void
     */
    public function __construct(MasterDataStore $store)
    {
        parent::__construct();

        $this->store = $store;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->store->getApplicants() as $applicant) {
            if ($applicant->wuser_username) {
                continue;
            }

            list($user, $password) = $this->store->createApplicantUser($applicant);

            if ($user !== null && $password !== null) {
                $this->info('User created for applicant '.$user.': '.$password);
            } else {
                $this->info('No user created for ' . $applicant->id);
            }

            return 0;
        }
    }
}
