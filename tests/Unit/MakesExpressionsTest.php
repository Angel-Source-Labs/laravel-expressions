<?php


namespace Tests\Unit;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Expression;
use Orchestra\Testbench\TestCase;
use Tests\Fixtures\InetAton;
use Tests\Fixtures\Point;

class MakesExpressionsTest extends TestCase
{
    use MakesExpressions;

    public function assertExpression($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(Expression::class, $expression);
            $this->assertNotInstanceOf(ExpressionWithBindings::class, $expression);
            $this->assertNotInstanceOf(IsExpression::class, $expression);
            $this->assertNotInstanceOf(HasBindings::class, $expression);
        }
    }

    public function assertIsExpression($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertNotInstanceOf(Expression::class, $expression);
            $this->assertNotInstanceOf(ExpressionWithBindings::class, $expression);
            $this->assertInstanceOf(IsExpression::class, $expression);
            $this->assertNotInstanceOf(HasBindings::class, $expression);
        }
    }

    public function assertExpressionWithBindings($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(Expression::class, $expression);
            $this->assertInstanceOf(ExpressionWithBindings::class, $expression);
            $this->assertInstanceOf(IsExpression::class, $expression);
            $this->assertInstanceOf(HasBindings::class, $expression);
        }
    }

    public function assertIsExpressionHasBindings($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertNotInstanceOf(Expression::class, $expression);
            $this->assertNotInstanceOf(ExpressionWithBindings::class, $expression);
            $this->assertInstanceOf(IsExpression::class, $expression);
            $this->assertInstanceOf(HasBindings::class, $expression);
        }
    }

    public function assertExpressionHasGrammar($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertTrue($expression instanceof Expression || $expression instanceof IsExpression,
            "Expression is not instance of Expression or IsExpression.  Expression is instance of " .
            (is_object($expression) ?
                "is instance of " . get_class($expression) :
                "is of type " . gettype($expression))
            );
            $this->assertTrue($expression->getValue() instanceof Grammar,
            "Expression does not have grammar.  expression->getValue() " .
            (is_object($expression->getValue()) ?
                "is instance of " . get_class($expression->getValue()) :
                "is of type " . gettype($expression->getValue()))
            );
            $this->assertNull($expression->getValue()->driver(), "Grammar driver is not null in initial state.");
            $expression->getValue()->driver("mysql");
            $this->assertEquals($expression->getValue()->driver(), "mysql", "Grammar driver did not set driver properly.");
        }
    }

    public function test_makeExpressions_no_bindings()
    {
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression');
        $this->assertExpression([$expression, $expressionWithGrammar]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_bindings()
    {
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression (?,?)', [200, 100]);
        $this->assertExpressionWithBindings([$expression, $expressionWithGrammar]);
        $this->assertIsExpressionHasBindings([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_empty_bindings()
    {
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression (?,?)', []);
        $this->assertExpressionWithBindings([$expression, $expressionWithGrammar]);
        $this->assertIsExpressionHasBindings([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_domain_ExpressionWithBindings()
    {
        $inet = new InetAton("192.168.0.1");
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression] = $this->makeExpressions($inet);
        $this->assertExpressionWithBindings([$expression, $expressionWithGrammar, $originalExpression]);
        $this->assertIsExpressionHasBindings([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
        $this->assertInstanceOf(InetAton::class, $originalExpression);
    }

    public function test_makeExpresions_with_Point_fixture()
    {
        $point = new Point(12, 34);
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression] = $this->makeExpressions($point);
        $this->assertExpressionWithBindings([$expression, $expressionWithGrammar]);
        $this->assertIsExpressionHasBindings([$isExpression, $isExpressionWithGrammar, $originalExpression]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar, $originalExpression]);
        $this->assertInstanceOf(Point::class, $originalExpression);
    }

}