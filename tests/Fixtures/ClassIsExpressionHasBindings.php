<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ProvidesExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ProvidesExpression;

/**
 * Class ClassIsExpressionHasBindings
 * @package Tests\Fixtures
 *
 * Test fixture to test a class that implements IsExpression and HasBindings and does not extend ExpressionWithBindings.
 * Typical use case would be to use these interfaces on classes that already extend from a different parent class.
 * Typically in a situation where the class does not extend from a parent it would be better to extend ExpressionWithBindings.
 *
 * This test fixture is essentially a reimplementation of ExpressionWithBindings
 */
class ClassIsExpressionHasBindings implements IsExpression, HasBindings
{
    use ProvidesExpressionWithBindings;
}