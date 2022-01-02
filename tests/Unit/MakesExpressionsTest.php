<?php


namespace Tests\Unit;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IdentifiesExpressions;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Expression as BaseExpression;
use Orchestra\Testbench\TestCase;
use Tests\Fixtures\InetAton;
use Tests\Fixtures\Point;

class MakesExpressionsTest extends TestCase
{
    use MakesExpressions, IdentifiesExpressions;

    public function assertExpressionHasNoBindings($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertTrue($this->isExpression($expression));
            $this->assertFalse($this->isExpressionWithBindings($expression));
        }
    }

    public function assertIsExpression($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertNotInstanceOf(Expression::class, $expression);
            $this->assertNotInstanceOf(BaseExpression::class, $expression);
            $this->assertInstanceOf(IsExpression::class, $expression);
        }
    }

    public function assertExpressionHasBindings($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(IsExpression::class, $expression);
            $this->assertTrue($this->isExpressionWithBindings($expression));
        }
    }

    public function assertExpressionHasGrammar($expressions)
    {
        $expressions = is_array($expressions) ? $expressions : [$expressions];
        foreach ($expressions as $expression) {
            $this->assertTrue($this->isExpression($expression),
            "Expression is not instance of Expression or IsExpression.  Expression is instance of " .
            (is_object($expression) ?
                "is instance of " . get_class($expression) :
                "is of type " . gettype($expression))
            );
            $this->assertTrue($this->isExpressionWithGrammar($expression),
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
        [$baseExpression, $expression, $isExpression, $baseExpressionWithGrammar, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression');
        $this->assertExpressionHasNoBindings([$baseExpression, $expression, $isExpression, $baseExpressionWithGrammar, $expressionWithGrammar, $isExpressionWithGrammar]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$baseExpressionWithGrammar, $expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_bindings()
    {
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression (?,?)', [200, 100]);
        $this->assertExpressionHasBindings([$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_empty_bindings()
    {
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar] = $this->makeExpressions('sql expression (?,?)', []);
        $this->assertExpressionHasNoBindings([$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
    }

    public function test_makeExpressions_with_semantic_expression_with_bindings()
    {
        $inet = new InetAton("192.168.0.1");
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression] = $this->makeExpressions($inet);
        $this->assertExpressionHasBindings([$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar]);
        $this->assertInstanceOf(InetAton::class, $originalExpression);
    }

    public function test_makeExpresions_with_Point_fixture()
    {
        $point = new Point(12, 34);
        [$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression] = $this->makeExpressions($point);
        $this->assertExpressionHasBindings([$expression, $isExpression, $expressionWithGrammar, $isExpressionWithGrammar, $originalExpression]);
        $this->assertIsExpression([$isExpression, $isExpressionWithGrammar, $originalExpression]);
        $this->assertExpressionHasGrammar([$expressionWithGrammar, $isExpressionWithGrammar, $originalExpression]);
        $this->assertInstanceOf(Point::class, $originalExpression);
    }

}