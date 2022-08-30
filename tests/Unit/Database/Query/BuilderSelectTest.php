<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderSelectTest extends BaseTestCase
{
    public function test_Select_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions("price * ? as price_with_tax", [1.0825]) as $expression)
        {
            DB::table('orders')->select($expression)->get();
            $this->assertEquals('select price * ? as price_with_tax from `orders`', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(1, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => "1.0825"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}