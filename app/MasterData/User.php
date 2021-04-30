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

namespace App\MasterData;

use App\Notifications\ResetPassword;
use App\Support\Activity\Log;
use App\Tasting\TastingSession;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * @property string $username
 * @property string $password
 * @property bool $admin
 * @property string $remember_token
 * @property string $email
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * table name.
     *
     * @var string
     */
    protected $table = 'wuser';

    /**
     * primary key of table user.
     *
     * @var string
     */
    protected $primaryKey = 'username';

    /**
     * The primary key is a VARCHAR, hence it does not need auto-incrementing.
     *
     * This fix the bug where the primary key was casted to int, which caused
     * login errors.
     *
     * @see https://github.com/laravel/framework/pull/12067/files
     * @see https://github.com/laravel/framework/issues/11484
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * attributes allowed for mass assignment.
     *
     * @var array of string
     */
    protected $fillable = [
        'username',
        'password',
        'admin',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'admin' => 'boolean',
    ];

    public function administrates(self $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->username === $this->username;
    }

    /**
     * Password mutator.
     *
     * @param string $password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * 1 user : n activity logs.
     *
     * @return Relation
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'wuser_username', 'username');
    }

    /**
     * 1 user : n applicants.
     *
     * @return Relation
     */
    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'wuser_username', 'username');
    }

    /**
     * 1 user : n associations : m applicants
     * => 1 user : n*m applicants.
     *
     * @return Relation
     */
    public function associationApplicants()
    {
        return $this->hasManyThrough(Applicant::class, Association::class, 'wuser_username');
    }

    /**
     * 1 user : n associations relation.
     *
     * @return Relation
     */
    public function associations()
    {
        return $this->hasMany(Association::class, 'wuser_username', 'username');
    }

    /**
     * 1 user : n competitions.
     *
     * @return Relation
     */
    public function competitions()
    {
        return $this->hasMany(Competition::class, 'wuser_username', 'username');
    }

    /**
     * 1 user : n tasting sessions.
     *
     * @return Relation
     */
    public function tastingsessions()
    {
        return $this->hasMany(TastingSession::class, 'wuser_username', 'username');
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get token value.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set token value.
     *
     * @param string $value
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get token attribute name.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    public function getEmailForPasswordReset()
    {
        // User is assoc admin -> only check assocs
        $associationWithEmail = $this->associations()->whereNotNull('email')->first();
        if ($associationWithEmail !== null) {
            return $associationWithEmail->email;
        }

        // User might be applicant admin -> check those
        $applicantWithEmail = $this->applicants()->whereNotNull('email')->first();
        if ($applicantWithEmail !== null) {
            return $applicantWithEmail->email;
        }

        throw new \Exception('User has no email addresses');
	}

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function routeNotificationForMail()
    {
        return $this->getEmailForPasswordReset();
    }

    /**
     * Save way to determine if a user is admin.
     *
     * Catches null values (result in non-admin user state)
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin === true;
    }

    /**
     * Check whether two user objects are referencing the same data set.
     *
     * @param User $other
     * @return bool
     */
    public function is($other)
    {
        return $other instanceof self && $this->username === $other->username;
    }
}
