<?php

namespace EmilMoe\Eloquence;

trait PropertyObserver
{
    /**
     * Observed fillables
     *
     * @var array
     */
    private $fillableObserver = [];

    /**
     * Observed casts
     *
     * @var array
     */
    private $castsObserver = [];

    /**
     * This method is called upon instantiation of the Eloquent Model.
     *
     * @return void
     */
    public function initializePropertyObserver()
    {
        $this->addFillable($this->fillable ?? []);
        $this->addCasts($this->casts ?? []);
    }

    /**
     * Allow for traits to add fillables.
     *
     * @param array $fillable
     */
    protected function addFillable(array $fillable = []): void
    {
        $this->fillableObserver = array_merge($this->fillableObserver, $fillable);
    }

    /**
     * Return the mutated version of fillable.
     *
     * @return array|null
     */
    public function getFillable(): ?array
    {
        return $this->fillableObserver;
    }

    /**
     * Allow for traits to add fillables.
     *
     * @param array $casts
     */
    protected function addCasts(array $casts = []): void
    {
        $this->castsObserver = array_merge($this->castsObserver, $casts);
    }

    /**
     * Return the mutated version of fillable.
     *
     * @return array|null
     */
    public function getCasts(): ?array
    {
        return $this->castsObserver;
    }
}
