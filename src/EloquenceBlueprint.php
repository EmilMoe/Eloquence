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
    public function tableLock(): void
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
}