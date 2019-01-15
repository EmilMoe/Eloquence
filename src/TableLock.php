<?php

namespace EmilMoe\Eloquence;

use Illuminate\Support\Facades\DB;
use EmilMoe\Eloquence\Exceptions\UpdatingLockedRecordException;

/**
 * This feature allows to create appliationwise locks on columns for a record.
 *
 * When the given columns are locked, they won't be editable by any users except
 * for system users or if the scope ignoreLock is used.
 */
trait TableLock
{
    /**
     * Lockable columns for table.
     * Per default nothing is lockable,
     * must be implemented in model class.
     *
     * @var array
     */
    protected $lockable = [];

    /**
     * The "booting" method of the model.
     *
     * @throws UpdatingLockedRecordException
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($table) {
            if ($table->isLocked() && ! $this->isSystemUser() && $table->__ignore_locked !== true) {
                throw new UpdatingLockedRecordException;
            }
        });

        static::deleting(function ($table) {
            if ($table->isLocked() && ! $this->isSystemUser() && $table->__ignore_locked !== true) {
                throw new UpdatingLockedRecordException;
            }
        });
    }

    /**
     * Lock record columns.
     *
     * @param User $user
     */
    public function lock($user = null)
    {
        if ($user && ! $user instanceof config('auth.providers.users.model')) {
            abort(500, '$user must be instance of '. config('auth.providers.users.model'));
        }

        if ($user) {
            $this->attributes['locked_by_id'] = $user->id;
        }

        $this->attributes['is_locked'] = true;
        $this->save();
    }

    /**
     * Unlock record columns.
     */
    public function unlock()
    {
        $this->attributes['locked_by_id'] = null;
        $this->attributes['is_locked']    = false;
        $this->save();
    }

    /**
     * Does the record have locked columns.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->attributes['locked'];
    }

    /**
     * Who locked the record columns.
     *
     * @return User
     */
    public function lockedBy()
    {
        return $this->belongsTo((config('auth.providers.users.model')), 'locked_by_id');
    }

    /**
     * Ignore locked state.
     *
     * @Builder $query
     */
    public function scopeIgnoreLock($query)
    {
        $query->addSelect(DB::raw("'__ignore_locked' AS true"));
    }

    /**
     * If system supports system users, check if the user is a system user.
     * Columns are never locked for a system user.
     *
     * @return bool
     */
    private function isSystemUser(): bool
    {
        if (! method_exists(Auth::user(), 'isSystemUser')) {
            return false;
        }

        return Auth::user()->isSystemUser();
    }
}