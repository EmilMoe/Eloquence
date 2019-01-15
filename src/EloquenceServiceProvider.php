<?php

namespace EmilMoe\Eloquence;

use Illuminate\Support\ServiceProvider;

class EloquenceServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $schema = DB::getSchemaBuilder();

        $schema->blueprintResolver(function($table, $callback) {
            return new EloquenceBlueprint($table, $callback);
        });
    }
}
