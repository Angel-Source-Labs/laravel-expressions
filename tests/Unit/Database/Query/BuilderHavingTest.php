<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderHavingTest extends BaseTestCase
{
    public function test_Having_using_Expression()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('IF(state = "TX", 200, 100)') as $expression)
        {
            DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->having('SUM(price)', '>', $expression)
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having `SUM(price)` > IF(state = "TX", 200, 100)', $this->pdo->queries[$queryIndex]);
            $this->assertEmpty($this->pdo->bindings, "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_Having_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('IF(state = "TX", ?, ?)', [200, 100]) as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->having('SUM(price)', '>', $expression)
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having `SUM(price)` > IF(state = "TX", ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}