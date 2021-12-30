<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionGrammar;
use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;


class BuilderWhereRawTest extends BaseTestCase
{
    public function test_WhereRaw_using_Expression()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > IF(state = "TX", 200, 100)') as $expression) {
            DB::table('orders')->whereRaw($expression)->get();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", 200, 100)', $this->pdo->queries[$queryIndex]);
            $this->assertEmpty($this->pdo->bindings, "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_WhereRaw_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > IF(state = "TX", ?, 100)', [200]) as $expression) {
            DB::table('orders')->whereRaw($expression)->get();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, 100)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(1, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 200], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_WhereRaw_with_bindings_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('price > IF(state = "TX", ?, ?)', [200]) as $expression) {
            DB::table('orders')->whereRaw($expression, [100])->get();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(2, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_WhereRaw_using_Expression_with_Grammar()
    {
        $driver = DB::connection()->getDriverName();

        $grammar = ExpressionGrammar::make()
            ->mySql('grammar = "mysql"')
            ->postgres('grammar = "pgsql"')
            ->sqLite('grammar = "sqlite"')
            ->sqlServer('grammar = "sqlserver"');

        $queryIndex = 0;
        foreach ($this->makeExpressions($grammar) as $expression) {
            DB::table('users')->whereRaw($expression)->get();
            $this->assertEquals('select * from `users` where grammar = "' . $driver . '"', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(0, count($this->pdo->bindings), "Incorrect number of bindings");
            $queryIndex++;
        }
    }

    public function test_WhereRaw_using_ExpressionWithBindings_with_Grammar()
    {
        $driver = DB::connection()->getDriverName();

        $grammar = ExpressionGrammar::make()
            ->mySql('grammar = "mysql" and price > IF(state = "TX", ?, ?)')
            ->postgres('grammar = "pgsql" and price > IF(state = "TX", ?, ?)')
            ->sqLite('grammar = "sqlite" and price > IF(state = "TX", ?, ?)')
            ->sqlServer('grammar = "sqlserver" price > IF(state = "TX", ?, ?)');

        $queryIndex = 0;
        foreach ($this->makeExpressions($grammar, [100, 200]) as $expression) {
            $sql = DB::table('users')->whereRaw($expression)->get();
            $this->assertEquals('select * from `users` where grammar = "' . $driver . '" and price > IF(state = "TX", ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals(2, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100, 2 => 200], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}