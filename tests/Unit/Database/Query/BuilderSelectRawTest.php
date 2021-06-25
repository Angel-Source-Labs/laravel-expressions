<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderSelectRawTest extends BaseTestCase
{
    public function test_SelectRaw_using_Expression()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions("price as price_before_tax") as $expression)
        {
            DB::table('orders')->selectRaw($expression)->get();
            $this->assertEquals('select price as price_before_tax from `orders`', $this->pdo->queries[$queryIndex]);
            $this->assertEmpty($this->pdo->bindings, "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_SelectRaw_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions("price * ? as price_with_tax", [1.0825]) as $expression)
        {
            DB::table('orders')->selectRaw($expression)->get();
            $this->assertEquals('select price * ? as price_with_tax from `orders`', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "1.0825"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
        }
    }

    public function test_SelectRaw_with_bindings_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions("price * ? as price_with_tax, price * ? as profit", [1.0825]) as $expression)
        {
            DB::table('orders')->selectRaw($expression, [.20])->get();
            $this->assertEquals('select price * ? as price_with_tax, price * ? as profit from `orders`', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 1.0825, 2 => 0.20], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
        }
    }
}