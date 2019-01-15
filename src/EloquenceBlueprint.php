<?php

namespace EmilMoe\Eloquence;

use Illuminate\Database\Schema\Blueprint;

class EloquenceBlueprint extends Blueprint
{
    /**
     * Add a locked columns for the table.
     *
     * @return void
     */
    public function lockable(): void
    {
        $this->boolean('is_locked')
            ->default(false);
        
        $this->integer('locked_by_id')
            ->unsigned()
            ->nullable()
            ->default(null);
        
        $this->foreign('locked_by_id')
            ->references('id')
            ->on(config('auth.providers.users.model'))
            ->onDelete('restrict');
    }

    /**
     * Drop lockable option.
     *
     * @return void
     */
    public function dropLockable(): void
    {
        $this->dropForeign('locked_by_id');
        $this->dropColumn('locked_by_id');
        $this->dropColumn('is_locked');
    }
}