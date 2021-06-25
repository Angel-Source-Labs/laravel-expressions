<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;

class BuilderWhereDateBasedTest extends BaseTestCase
{
    public function test_whereDate_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('DATE_ADD(?, ?)', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereDate('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where date(`created_at`) = DATE_ADD(?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereMonth_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('MONTH(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereMonth('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where month(`created_at`) = MONTH(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereDay_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('DAY(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereDay('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where day(`created_at`) = DAY(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereYear_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('YEAR(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereYear('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where year(`created_at`) = YEAR(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereTime_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('ADDTIME(?, ?)', ['11:20:45', "10:00"]) as $expression) {
            DB::table('audits')
                ->whereTime('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where time(`created_at`) = ADDTIME(?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "11:20:45", 2 => "10:00"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}