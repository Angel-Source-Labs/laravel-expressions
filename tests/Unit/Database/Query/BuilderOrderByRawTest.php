<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderOrderByRawTest extends BaseTestCase
{
    public function test_OrderByRaw_using_Expression()
    {
        $ids = [12,23,34,45];
        $queryIndex = 0;
        foreach ($this->makeExpressions('field(id, 12, 23, 34, 45)') as $expression)
        {
            DB::table('orders')
                ->whereIn('id', $ids)
                ->orderByRaw($expression)
                ->get();
            $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, 12, 23, 34, 45)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 12, 2 => 23, 3 => 34, 4 => 45], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_OrderByRaw_using_ExpressionWithBindings()
    {
        $ids = [12,23,34,45];
        $queryIndex = 0;
        foreach ($this->makeExpressions('field(id, ?, ?, ?, ?)', $ids) as $expression)
        {
            DB::table('orders')
                ->whereIn('id', $ids)
                ->orderByRaw($expression)
                ->get();
            $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(8, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_OrderByRaw_with_bindings_using_ExpressionWithBindings()
    {
        $ids = [12,23,34,45];
        $queryIndex = 0;
        foreach ($this->makeExpressions('field(id, ?, ?, ?, ?)', [12,23]) as $expression)
        {
            DB::table('orders')
                ->whereIn('id', $ids)
                ->orderByRaw($expression, [34, 45])
                ->get();
            $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(8, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}