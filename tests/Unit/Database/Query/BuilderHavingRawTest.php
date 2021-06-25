<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderHavingRawTest extends BaseTestCase
{
    public function test_HavingRaw_using_Expression()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('SUM(price) > 2500') as $expression)
        {
            DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression)
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > 2500', $this->pdo->queries[$queryIndex]);
            $this->assertEmpty($this->pdo->bindings, "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_HavingRaw_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('SUM(price) > ?', [2500]) as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression)
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ?', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 2500], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_HavingRaw_with_bindings_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('SUM(price) > ? and AVG(price) > ?', [2500]) as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression, [100])
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ? and AVG(price) > ?', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 2500, 2 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}