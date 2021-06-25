<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Fixtures\InetAton;
use Tests\Unit\BaseTestCase;


class BuilderDeleteTest extends BaseTestCase
{
    public function test_delete_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression) {
            DB::table('audits')->where('ip', $expression)->delete();
            $this->assertEquals('delete from `audits` where `ip` = inet_aton(?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "192.168.0.1"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_delete_using_ExpressionWithBindings_with_two_bindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('IF(state = "TX", ?, ?)', [200, 100]) as $expression) {
            DB::table('orders')->where('price', '>', $expression)->delete();
            $this->assertEquals('delete from `orders` where `price` > IF(state = "TX", ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}