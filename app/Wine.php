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

namespace App;

use App\MasterData\Applicant;
use App\MasterData\Competition;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Tasting\TastingNumber;
use App\Tasting\TastingStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $nr
 * @property int $competition_id
 * @property Competition $competition
 * @property int $applicant_id
 * @property Applicant $applicant
 * @property int $winesort_id
 * @property WineSort $winesort
 * @property int $winequality_id
 * @property WineQuality $winequality
 * @property string $label
 * @property int $vintage
 * @property float $alcohol
 * @property float $acidity
 * @property float $sugar
 * @property string $approvalnr
 * @property bool $kdb
 * @property bool $sosi
 * @property bool $chosen
 * @property bool $excluded
 * @property string $comment
 * @property bool $kdb_certificate
 */
class Wine extends Model implements AdministrateModel
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'wine';

    /**
     * Attributes for mass assignment.
     *
     * @var array of string
     */
    protected $fillable = [
        'nr',
        'competition_id',
        'applicant_id',
        'association_id',
        'label',
        'winesort_id',
        'winequality_id',
        'vintage',
        'alcohol',
        'acidity',
        'sugar',
        'kdb',
        'sosi',
        'chosen',
        'excluded',
        'catalogue_number',
        'kdb_certificate',
    ];

    /**
     * The attributes that should be hidden for arrays/json.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get next possible id for insert.
     *
     * @param Competition $competition
     * @return int
     */
    public static function maxId(Competition $competition)
    {
        if ($competition->wines->count() === 0) {
            return 0;
        }

        return $competition->wines->max('nr');
    }

    /**
     * Check if the given user is authorized to administrate.
     *
     * @param User $user
     * @return bool
     */
    public function administrates(User $user)
    {
        if ($user->isAdmin()) {
            // Sys admin
            return true;
        }
        if ($this->competition->administrates($user)) {
            // Competition admin
            return true;
        }
        if ($this->applicant->administrates($user)) {
            // Applicant admin
            return true;
        }

        return false;
    }

    /**
     * n wines : 1 applicant.
     *
     * @return Relation
     */
    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * n wines : 1 competition.
     *
     * @return Relation
     */
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * 1 wine : n tasting numbers.
     *
     * @return Relation
     */
    public function tastingnumbers()
    {
        return $this->hasMany(TastingNumber::class);
    }

    /**
     * n wines : 1 winesort.
     *
     * @return Relation
     */
    public function winesort()
    {
        return $this->belongsTo(WineSort::class);
    }

    /**
     * n wines : 1 winequality.
     *
     * @return Relation
     */
    public function winequality()
    {
        return $this->belongsTo(WineQuality::class);
    }

    /**
     * Scope kdb wines.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeKdb($query)
    {
        return $query->whereKdb(true);
    }

    /**
     * Scope excluded wines.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExcluded($query)
    {
        return $query->whereExcluded(true);
    }

    /**
     * Scope sosi wines.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSosi($query)
    {
        return $query->whereSosi(true);
    }

    /**
     * Scope chosen wines.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeChosen($query)
    {
        return $query->whereChosen(true);
    }

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function scopeAdmin($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        }
        $applicants = $user->applicants()->pluck('id');
        $associations = $user->associations()->pluck('id');

        return $query->whereIn('applicant_id', $applicants)
                ->OrWhereIn('association_id', $associations);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithFlaws($query)
    {
        return $query->whereNotNull('comment');
    }

    /**
     * scope wines with tasting number of specified tasting stage.
     *
     * @param Builder $query
     * @param TastingStage $ts
     * @return Builder
     */
    public function scopeWithTastingNumber($query, TastingStage $ts)
    {
        return $query->whereExists(function ($query) use ($ts) {
            $query->select(DB::raw(1))
                    ->from('tastingnumber')
                    ->where('wine_id', '=', DB::raw($this->table.'.id'))
                    ->where('tastingstage_id', '=', $ts->id);
        });
    }
}
