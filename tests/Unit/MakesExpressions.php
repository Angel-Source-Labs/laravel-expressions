<?php


namespace Tests\Unit;


use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Expression as BaseExpression;
use Tests\Fixtures\ClassIsExpression;

trait MakesExpressions
{
    /*
     * Return the following classes for tests:
     * 1. Expression with string value
     * 2. Class implementing IsExpression with string value
     * 3. Expression with ExpressionGrammar value
     * 4. Class implementing IsExpression with ExpressionGrammar value
     *
     * if $bindings are specified, construct the expression with bindings.  Otherwise call the constructor without bindings.
     */
    public function makeExpressions($sql, $bindings = null)
    {
        $expression = null;
        if ($sql instanceof BaseExpression || $sql instanceof IsExpression) {
            $expression = $sql;
            $sql = $sql->getValue();
        }

        $bindings = !isset($bindings) && $expression instanceof IsExpression && $expression->hasBindings() ? $expression->getBindings() : $bindings;
        $grammar = ($sql instanceof ExpressionGrammar) ?
            $sql :
            ExpressionGrammar::make()->mySql($sql)->postgres($sql)->sqLite($sql)->sqlServer($sql);

        if (isset($bindings)) {
            $expressions = [
                new Expression($sql, $bindings),
                new ClassIsExpression($sql, $bindings),
                new Expression(clone $grammar, $bindings),
                new ClassIsExpression(clone $grammar, $bindings)
            ];
        }
        else {
            $expressions = [
                new BaseExpression($sql),
                new Expression($sql),
                new ClassIsExpression($sql),
                new BaseExpression(clone $grammar),
                new Expression(clone $grammar),
                new ClassIsExpression(clone $grammar)
            ];
        }

        if (isset($expression)) {
            $expressions[] = $expression;
        }

        return $expressions;
    }
}