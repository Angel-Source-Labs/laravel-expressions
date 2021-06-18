<?php


namespace Tests\Unit;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\ClassIsExpression;
use Tests\Fixtures\ClassIsExpressionHasBindings;

trait MakesExpressions
{
    public function makeExpressions($sql, $bindings = null)
    {
        $expression = null;
        if ($sql instanceof Expression || $sql instanceof IsExpression) {
            $expression = $sql;
            $sql = $sql->getValue();
        }

        $bindings = !isset($bindings) && $expression instanceof HasBindings ? $expression->getBindings() : $bindings;
        $grammar = ($sql instanceof Grammar) ?
            $sql :
            Grammar::make()->mySql($sql)->postgres($sql)->sqLite($sql)->sqlServer($sql)->driver(DB::connection()->getDriverName());

        if (isset($bindings)) {
            $expressions = [
                new ExpressionWithBindings($sql, $bindings),
                new ClassIsExpressionHasBindings($sql, $bindings),
                new ExpressionWithBindings($grammar, $bindings),
                new ClassIsExpressionHasBindings($grammar, $bindings)
            ];
        }
        else {
            $expressions = [
                new Expression($sql),
                new ClassIsExpression($sql),
                new Expression($grammar),
                new ClassIsExpression($grammar)
            ];
        }

        if (isset($expression)) {
            $expressions[] = $expression;
        }

        return $expressions;
    }
}