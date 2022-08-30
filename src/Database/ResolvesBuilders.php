<?php


namespace AngelSourceLabs\LaravelExpressions\Database;

use Exception;
use Illuminate\Database\Query\Builder;

trait ResolvesBuilders
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

    /**
     * Get a schema builder instance for the connection from the container.
     *
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        $builder = parent::getSchemaBuilder();

        try {
            return resolve(get_class($builder), ['connection' => $this]);
        }
        catch (Exception $e) {
            return $builder;
        }
    }
}