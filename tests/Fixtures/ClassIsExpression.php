<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;

/**
 * Class ClassIsExpressionHasBindings
 * @package Tests\Fixtures
 *
 * Test fixture to test a class that implements IsExpression and does not extend Expression.
 * Typical use case would be to use these interfaces on classes that already extend from a different parent class.
 * Typically in a situation where the class does not extend from a parent it would be better to extend Expression or ExpressionWithBindings.
 *
 * This test fixture is essentially a reimplementation of Expression
 */
class ClassIsExpression implements IsExpression
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string) $this->getValue();
    }
}