<?php


namespace AngelSourceLabs\LaravelExpressions\Eloquent;

use AngelSourceLabs\LaravelExpressions\HasExpression;
use AngelSourceLabs\LaravelExpressions\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait HasExpressionAttributes
{
    public $attributesWithExpression = [];

    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    protected function performInsert(EloquentBuilder $query, array $options = [])
    {
        foreach ($this->attributes as $key => $value) {
            if ($value instanceof HasExpression) {
                $this->attributesWithExpression[$key] = $value; //Preserve the objects with expressions prior to the insert
                $this->attributes[$key] = $value->getExpression();
            }
        }

        $insert = parent::performInsert($query, $options);

        foreach ($this->attributesWithExpression as $key => $value) {
            $this->attributes[$key] = $value; //Restore the objects with expressions so they can be used in the model
        }

        return $insert; //Return the result of the parent insert
    }

    protected function performUpdate(EloquentBuilder $query)
    {
        foreach ($this->getDirty() as $key => $value) {
            if ($value instanceof HasExpression) {
                $this->attributesWithExpression[$key] = $value; //Preserve the objects with expressions prior to the insert
                $this->attributes[$key] = $value->getExpression();
            }
        }

        $update = parent::performUpdate($query);

        foreach ($this->attributesWithExpression as $key => $value) {
            $this->attributes[$key] = $value; //Restore the objects with expressions so they can be used in the model
        }

        return $update; //Return the result of the parent insert
    }
}