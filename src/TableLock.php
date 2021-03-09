<?php

namespace EmilMoe\Eloquence;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
            if ($table->isLocked() && ! self::isSystemUser() && $table->__ignore_locked !== true) {
                if (self::lockedAttributeChanged($table)) {
                    throw new UpdatingLockedRecordException();
                }
            }
        });

        static::deleting(function ($table) {
            if ($table->isLocked() && ! self::isSystemUser() && $table->__ignore_locked !== true) {
                throw new UpdatingLockedRecordException();
            }
        });
    }

    /**
     * Expose lockable attributes.
     *
     * @return array
     */
    public function getLockables(): array
    {
        return $this->lockable;
    }

    /**
     * Lock record columns.
     *
     * @param User $user
     */
    public function lock($user = null)
    {
        $userClass = config('auth.providers.users.model');
        
        if ($user && ! $user instanceof $userClass) {
            abort(500, '$user must be instance of '. config('auth.providers.users.model'));
        }

        if ($user) {
            $this->attributes['locked_by_id'] = $user->id;
        }

        $this->attributes['locked'] = true;
        $this->save();
    }

    /**
     * Unlock record columns.
     */
    public function unlock()
    {
        $this->attributes['locked_by_id'] = null;
        $this->attributes['locked']       = false;
        $this->save();
    }

    /**
     * Get locked status as boolean.
     *
     * @return bool
     */
    public function getLockedAttribute(): bool
    {
        if (isset($this->attributes['locked'])) {
            return $this->attributes['locked'] === 1;
        }

        return self::find($this->id)->attributes['locked'] === 1;
    }

    /**
     * Does the record have locked columns.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
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
     * 
     */
    private static function lockedAttributeChanged($model)
    {
        $lockTouched = false;

        collect($model->getLockables())->first(function ($attribute) use (&$lockTouched, $model) {
            if ($model->{$attribute} != $model->original[$attribute]) {
                $lockTouched = true;
                return;
            }
        });

        return $lockTouched;
    }

    /**
     * If system supports system users, check if the user is a system user.
     * Columns are never locked for a system user.
     *
     * @return bool
     */
    private static function isSystemUser(): bool
    {
        if (app()->runningInConsole()) {
            if (! Auth::check()) {
                return true;
            }
        }
        
        $userClass = config('auth.providers.users.model');

        if (! method_exists($userClass, 'isSystemUser')) {
            return false;
        }
        
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->isSystemUser();
    }
}
