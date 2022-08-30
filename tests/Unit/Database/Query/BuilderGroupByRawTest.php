<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderGroupByRawTest extends BaseTestCase
{
    public function test_GroupByRaw_using_Expression()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > 100') as $expression)
        {
            DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression)
                ->get();
            $this->assertEquals('select `department`, `price` from `orders` group by price > 100', $this->pdo->queries[$queryIndex]);
            $this->assertEmpty($this->pdo->bindings, "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_GroupByRaw_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > ?', [100]) as $expression)
        {
            DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression)
                ->get();
            $this->assertEquals('select `department`, `price` from `orders` group by price > ?', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(1, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_GroupByRaw_with_bindings_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > ?, department > ?', [100]) as $expression) {
            DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression, [1560])
                ->get();
            $this->assertEquals('select `department`, `price` from `orders` group by price > ?, department > ?', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(2, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100, 2 => 1560], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}