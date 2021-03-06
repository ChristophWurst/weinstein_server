<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace App\Tasting;

use App\Contracts\TastingHandler;
use App\Database\Repositories\CommissionRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\TasterRepository;
use App\Database\Repositories\TastingNumberRepository;
use App\Database\Repositories\TastingRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\Database\Repositories\WineRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Validation\TastingNumberValidatorFactory;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Handler implements TastingHandler
{
    /** @var CommissionRepository */
    private $commissionRepository;

    /** @var CompetitionRepository */
    private $competitionRepository;

    /** @var TasterRepository */
    private $tasterRepository;

    /** @var TastingRepository */
    private $tastingRepository;

    /** @var TastingNumberRepository */
    private $tastingNumberRepository;

    /** @var TastingSessionRepository */
    private $tastingSessionRepository;

    /** @var WineRepository */
    private $wineRepository;

    /** @var TastingNumberValidatorFactory */
    private $tastingNumberValidatorFactory;

    /**
     * @param CommissionRepository $commissionRepository
     * @param CompetitionRepository $competitionRepository
     * @param TasterRepository $tasterRepository
     * @param TastingRepository $tastingRepository
     * @param TastingNumberRepository $tastingNumberRepository
     * @param TastingSessionRepository $tastingSessionRepository
     * @param WineRepository $wineRepository
     * @param TastingNumberValidatorFactory $tastingNumberValidatorFactory
     */
    public function __construct(CommissionRepository $commissionRepository, CompetitionRepository $competitionRepository,
        TasterRepository $tasterRepository, TastingRepository $tastingRepository,
        TastingNumberRepository $tastingNumberRepository, TastingSessionRepository $tastingSessionRepository,
        WineRepository $wineRepository, TastingNumberValidatorFactory $tastingNumberValidatorFactory)
    {
        $this->competitionRepository = $competitionRepository;
        $this->tasterRepository = $tasterRepository;
        $this->tastingRepository = $tastingRepository;
        $this->tastingNumberRepository = $tastingNumberRepository;
        $this->tastingSessionRepository = $tastingSessionRepository;
        $this->wineRepository = $wineRepository;
        $this->commissionRepository = $commissionRepository;
        $this->tastingNumberValidatorFactory = $tastingNumberValidatorFactory;
    }

    public function lockTastingNumbers(Competition $competition)
    {
        if (in_array($competition->competitionState->description, [
                'TASTINGNUMBERS1',
                'TASTINGNUMBERS2',
            ])) {
            $competition->competition_state_id += 1;
            $this->competitionRepository->update($competition);
        } else {
            throw new Exception('invalid competition state');
        }
        //ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $tasting . '. Kostnummernvergabe beendet');
    }

    public function lockTasting(Competition $competition)
    {
        if ($competition->competitionState->description === 'TASTING1') {
            $competition->competition_state_id += 1;
            $this->competitionRepository->update($competition);
        } elseif ($competition->competitionState->description === 'TASTING2') {
            $competition->competition_state_id += 1;
            $this->competitionRepository->update($competition);
        } else {
            throw new Exception('invalid competition state');
        }

        // close all sessions
        foreach ($competition->tastingsessions as $session) {
            $this->tastingSessionRepository->update($session, [
                'locked' => true,
            ]);
        }
        //$stateId = $state === 'TASTING1' ? 1 : 2;
        //ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $state . '. Verkostung beendet');
    }

    public function lockKdb(Competition $competition)
    {
        $competition->competition_state_id += 1;
        $this->competitionRepository->update($competition);
        //ActivityLogger::log('Bewerb [' . $competition->label . '] KdB Zuweisung beendet');
    }

    public function lockExcluded(Competition $competition)
    {
        $competition->competition_state_id += 1;
        $this->competitionRepository->update($competition);
        //ActivityLogger::log('Bewerb [' . $competition->label . '] Ausschluss beendet');
    }

    public function lockSosi(Competition $competition)
    {
        $competition->competition_state_id += 1;
        $this->competitionRepository->update($competition);
        //ActivityLogger::log('Bewerb [' . $competition->label . '] SoSi Zuweisung beendet');
    }

    public function lockChoosing(Competition $competition)
    {
        $competition->competition_state_id += 1;
        $this->competitionRepository->update($competition);
        //ActivityLogger::log('Bewerb [' . $competition->label . '] Auswahl beendet');
    }

    public function isTastingFinished(Competition $competition)
    {
        $tastingStage = $competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new Exception('invalid applicaton/competition/tasting stage');
        }

        return $this->getUntastedTastingNumbers($competition, $tastingStage)->count() === 0;
    }

    public function createTastingNumber(array $data, Competition $competition)
    {
        $this->tastingNumberValidatorFactory->newValidator($competition, $data)->validateCreate();

        $wine = $this->wineRepository->findByNr($competition, $data['wine_nr']);
        //competition's tasting stage is choosen by default
        $tastingStage = $competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new Exception('invalid applicaton/competition/tasting stage');
        }

        $tastingNumber = $this->tastingNumberRepository->create($data, $wine, $tastingStage);
        if ($competition->competitionState->id === CompetitionState::STATE_ENROLLMENT) {
            $competition->competition_state_id = CompetitionState::STATE_TASTINGNUMBERS1;
            $this->competitionRepository->update($competition);
        }

        return $tastingNumber;
    }

    public function importTastingNumbers(UploadedFile $file, Competition $competition)
    {
        //iterate over all entries and try to store them
        //if exceptions occur, all db actions are rolled back to prevent data
        //inconsistency
        try {
            $doc = IOFactory::load($file->getRealPath());
        } catch (Exception $ex) {
            throw new ValidationException(new MessageBag(['Ung&uuml;ltiges Dateiformat']));
        }

        $sheet = $doc->getActiveSheet();

        DB::beginTransaction();
        try {
            $rowCount = 1;

            foreach ($sheet->toArray() as $row) {
                if (! isset($row[0]) || ! isset($row[1])) {
                    Log::error('invalid tasting number import format');
                    throw new ValidationException(new MessageBag(['Fehler beim Lesen der Datei']));
                }
                $data = [
                    'nr' => $row[0],
                    'wine_nr' => $row[1],
                ];
                $this->createTastingNumber($data, $competition);
                $rowCount++;
            }
        } catch (ValidationException $ve) {
            DB::rollback();
            $messages = new MessageBag([
                'row' => 'Fehler in Zeile '.$rowCount,
            ]);
            $messages->merge($ve->getErrors());
            throw new ValidationException($messages);
        }
        if ($competition->competition_state_id === CompetitionState::STATE_ENROLLMENT) {
            $competition->competition_state_id = CompetitionState::STATE_TASTINGNUMBERS1;
            $this->competitionRepository->update($competition);
        }
        DB::commit();
        //return number of read lines
        return $rowCount - 1;
    }

    public function resetTastingNumbers(Competition $competition)
    {
        $tastingStage = $competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new Exception('invalid applicaton/competition/tasting stage');
        }
        $this->tastingNumberRepository->deleteAll($competition, $tastingStage);
    }

    public function deleteTastingNumber(TastingNumber $tastingNumber)
    {
        // TODO: check competition state
        $this->tastingNumberRepository->delete($tastingNumber);
    }

    /**
     * @param int $limit
     */
    public function getUntastedTastingNumbers(Competition $competition, TastingStage $tastingStage, $limit = 2)
    {
        return $this->tastingNumberRepository->findUntasted($competition, $tastingStage, $limit);
    }

    public function getAllTastingNumbers(Competition $competition, TastingStage $tastingStage)
    {
        return $this->tastingNumberRepository->findAllForCompetitionTastingStage($competition, $tastingStage);
    }

    public function getAllTastingSessions(Competition $competition, TastingStage $tastingStage, User $user = null)
    {
        if (is_null($user) || $user->isAdmin()) {
            return $this->tastingSessionRepository->findAll($competition, $tastingStage);
        }

        return $this->tastingSessionRepository->findForUser($competition, $tastingStage, $user);
    }

    public function createTastingSession(array $data, Competition $competition)
    {
        $validator = new TastingSessionValidator($data);
        $validator->setCompetition($competition);
        $validator->validateCreate();

        $tastingStage = $competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new Exception('invalid applicaton/competition/tasting stage');
        }
        $data['nr'] = $competition->tastingsessions()->ofTastingStage($tastingStage)->max('nr') + 1;
        $tastingSession = $this->tastingSessionRepository->create($data, $competition, $tastingStage);

        $this->createCommissions($tastingSession, (int) $data['commissions']);

        return $tastingSession;
    }

    /**
     * Create nr commissions for the given tasting session.
     *
     * @param TastingSession $tastingSession
     * @param int $nr
     */
    private function createCommissions(TastingSession $tastingSession, $nr)
    {
        $dataA = [
            'side' => 'a',
        ];
        $this->commissionRepository->create($dataA, $tastingSession);

        if ($nr === 2) {
            $dataB = [
                'side' => 'b',
            ];
            $this->commissionRepository->create($dataB, $tastingSession);
        }
    }

    public function updateTastingSession(TastingSession $tastingSession, array $data)
    {
        if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
            $data['wuser_username'] = null;
        }
        $validator = new TastingSessionValidator($data, $tastingSession);
        $validator->setCompetition($tastingSession->competition);
        $validator->validateUpdate();

        $this->tastingSessionRepository->update($tastingSession, $data);
    }

    public function lockTastingSession(TastingSession $tastingSession)
    {
        $this->tastingSessionRepository->update($tastingSession, [
            'locked' => true,
        ]);
    }

    public function deleteTastingSession(TastingSession $tastingSession)
    {
        $this->tastingSessionRepository->delete($tastingSession);
    }

    public function createTaster(array $data)
    {
        $validator = new TasterValidator($data);
        $validator->validateCreate();

        $commission = $this->commissionRepository->find($data['commission_id']);

        if ($commission->tasters()->orderBy('nr', 'desc')->first()) {
            //commission has existing tasters
            $data['nr'] = $commission->tasters()->orderBy('nr', 'desc')->first()->nr + 1;
        } else {
            $data['nr'] = 1;
        }
        $data['active'] = true;

        $taster = $this->tasterRepository->create($data, $commission);

        return $taster;
    }

    public function getCommissionTasters(Commission $commission)
    {
        return $this->tasterRepository->findForCommission($commission);
    }

    public function updateTaster(Taster $taster, array $data)
    {
        $validator = new TasterValidator($data, $taster);
        $validator->validateUpdate();

        return $this->tasterRepository->update($taster, $data);
    }

    public function createTasting(array $data, TastingSession $tastingSession): void
    {
        $nrCommissions = count($this->getNextTastingNumbers($tastingSession));
        $validator = new TastingValidator($data);
        $validator->setTastingSession($tastingSession);
        $validator->setNrOfCommissions($nrCommissions);
        $validator->validateCreate();

        foreach ($tastingSession->commissions as $commission) {
            if (($commission->side === 'b') && ($nrCommissions == 1)) {
                continue;
            }
            if ($commission->side === 'a') {
                $tastingNumber = $data['tastingnumber_id1'];
            } elseif ($commission->side === 'b') {
                $tastingNumber = $data['tastingnumber_id2'];
            } else {
                Log::error('Invalid commission side '.$commission->side);
                App::abort(500);

                return;
            }

            $tastingNumber = $this->tastingNumberRepository->find($tastingNumber);

            /*
             * store all tasters ratings
             *
             * ratings are divided by 10 because the input range is 10 to 50
             * because that is easier to enter
             */
            foreach ($this->tasterRepository->getActive($commission) as $taster) {
                $tastingData = [
                    'rating' => $data[$commission->side.$taster->nr] / 10,
                ];
                $this->tastingRepository->create($tastingData, $taster, $tastingNumber);
            }

            /*
             * Store comment
             */
            $commentKey = 'comment-'.$commission->side;
            $comment = isset($data[$commentKey]) ? $data[$commentKey] : '';
            $this->wineRepository->addComment($tastingNumber->wine, $comment);
        }
    }

    public function updateTasting(array $data, TastingNumber $tastingNumber, TastingSession $tastingSession,
        Commission $commission)
    {
        $validator = new TastingValidator($data, $tastingNumber);
        $validator->setTastingSession($tastingSession);
        $validator->setCommission($commission);
        $validator->validateUpdate();

        DB::beginTransaction();
        //delete existing tastings
        $this->tastingRepository->clear($tastingNumber);

        /*
         * store all tasters ratings
         *
         * ratings are divided by 10 because the input range is 10 to 50
         * because that is easier to enter
         */
        foreach ($this->tasterRepository->getActive($commission) as $taster) {
            $tastingData = [
                'rating' => $data[$commission->side.$taster->nr] / 10,
            ];
            $this->tastingRepository->create($tastingData, $taster, $tastingNumber);
        }

        /*
         * Store comment
         */
        $wine = $tastingNumber->wine;
        $wine->comment = isset($data['comment']) && ! empty($data['comment']) ? $data['comment'] : null;
        $wine->save();
        DB::commit();
    }

    public function getNextTastingNumbers(TastingSession $tastingSession)
    {
        $tastingStage = $tastingSession->competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new Exception('invalid applicaton/competition/tasting stage');
        }
        $tastingNumbers = $this->getUntastedTastingNumbers($tastingSession->competition, $tastingStage, 2);
        $data = [];
        if ($tastingNumbers->count() > 0) {
            $data['a'] = $tastingNumbers->get(0);
        }
        if ($tastingNumbers->count() > 1 && $tastingSession->commissions->count() > 1) {
            $data['b'] = $tastingNumbers->get(1);
        }

        return $data;
    }

    public function isTastingNumberTasted(TastingNumber $tastingNumber)
    {
        return $this->tastingNumberRepository->isTasted($tastingNumber);
    }
}
