<?php


namespace AngelSourceLabs\LaravelExpressions\Database;

use Illuminate\Database\Query\Builder;

trait ResolvesQueryBuilder
{
    /**
     * Get a new query builder instance from the container.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return resolve(Builder::class, [
            'connection' => $this,
            'grammar' => $this->getQueryGrammar(),
            'processor' => $this->getPostProcessor()
        ]);
    }
}