<?php

namespace EmilMoe\Eloquence;

use Illuminate\Database\Schema\Blueprint;

class EloquenceBlueprint
{
    /**
     * Add a locked columns for the table.
     *
     * @param Schema $table
     * @return void
     */
    public static function lockable(Blueprint $table): void
    {
        $table->boolean('is_locked')
            ->default(false);
        
        $table->integer('locked_by_id')
            ->unsigned()
            ->nullable()
            ->default(null);
        
        $table->foreign('locked_by_id')
            ->references('id')
            ->on((new (config('auth.providers.users.model')))->getTable())
            ->onDelete('restrict');
    }

    /**
     * Drop lockable option.
     *
     * @param Schema $table
     * @return void
     */
    public static function dropLockable(Blueprint $table): void
    {
        $table->dropForeign('user_activity_locked_by_id_foreign');
        $table->dropColumn('locked_by_id');
        $table->dropColumn('is_locked');
    }
}