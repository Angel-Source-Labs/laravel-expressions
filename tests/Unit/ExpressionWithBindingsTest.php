<?php


namespace Tests\Unit;


use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Tests\Fixtures\EarlyBindingPoint;
use Tests\Fixtures\Point;
use Tests\Fixtures\SpatialExpression;
use Tests\Unit\DatabaseConnections;

class ExpressionWithBindingsTest extends TestCase
{

    public function test_value_and_bindings()
    {
        $expression = new ExpressionWithBindings("ST_GeomFromText(?, ?, 'axis-order=long-lat')", ['POINT(1 1)', 4236]);
        $this->assertEquals("ST_GeomFromText(?, ?, 'axis-order=long-lat')", $expression->getValue());
        $this->assertEquals(['POINT(1 1)', 4236], $expression->getBindings());
    }

    public function test_callable_late_binding_evaluation()
    {
        $point = new Point(1, 1);
        $this->assertEquals("ST_GeomFromText(?, ?, 'axis-order=long-lat')", $point->getValue()->driver('mysql'));
        $this->assertEquals(['POINT(1 1)', 4236], $point->getBindings());

        $point->setLat(2);
        $this->assertEquals("ST_GeomFromText(?, ?, 'axis-order=long-lat')", $point->getValue());
        $this->assertEquals(['POINT(1 2)', 4236], $point->getBindings());
    }

    public function test_early_binding_evaluation()
    {
        $point = new EarlyBindingPoint(1, 1);
        $this->assertEquals("ST_GeomFromText(?, ?, 'axis-order=long-lat')", $point->getValue()->driver('mysql'));
        $this->assertEquals(['POINT(1 1)', 4236], $point->getBindings());

        $point->setLat(2);
        $this->assertEquals("ST_GeomFromText(?, ?, 'axis-order=long-lat')", $point->getValue());
        $this->assertEquals(['POINT(1 1)', 4236], $point->getBindings());
    }
}
