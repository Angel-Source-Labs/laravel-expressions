<?php

namespace Tests\Unit\Database\Query;

use AngelSourceLabs\LaravelExpressionGrammar\ExpressionGrammar;
use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;
use Tests\Unit\DatabaseConnections;

class GrammarTest extends BaseTestCase
{
    use DatabaseConnections;

    public function tearDown() : void
    {
        $this->pdo->resetQueries();
        parent::tearDown();
    }

    public function assertGrammar($sql)
    {
        $grammar = ExpressionGrammar::make()
            ->mySql('grammar = "mysql" and price > IF(state = "TX", ?, ?)')
            ->postgres('grammar = "pgsql" and price > IF(state = "TX", ?, ?)')
            ->sqLite('grammar = "sqlite" and price > IF(state = "TX", ?, ?)')
            ->sqlServer('grammar = "sqlserver" and price > IF(state = "TX", ?, ?)');

        $queryIndex = 0;
        foreach ($this->makeExpressions($grammar, [100, 200]) as $expression) {
            DB::table('users')->whereRaw($expression)->get();
            $this->assertEquals($sql, $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 100, 2 => 200], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_MySqlConnection_has_correct_insert_and_update_Sql()
    {
        $this->useMySqlConnection($this->app);
        $this->assertGrammar('select * from `users` where grammar = "mysql" and price > IF(state = "TX", ?, ?)');
    }

    public function test_PostgresConnection_has_correct_insert_and_update_Sql()
    {
        $this->usePostgresConnection($this->app);
        $this->assertGrammar('select * from "users" where grammar = "pgsql" and price > IF(state = "TX", ?, ?)');
    }

    public function test_useSQLiteConnection_has_correct_insert_and_update_Sql()
    {
        $this->useSQLiteConnection($this->app);
        $this->assertGrammar('select * from "users" where grammar = "sqlite" and price > IF(state = "TX", ?, ?)');
    }

    public function test_useSqlServerConnection_has_correct_insert_and_update_Sql()
    {
        $this->useSqlServerConnection($this->app);
        $this->assertGrammar('select * from [users] where grammar = "sqlserver" and price > IF(state = "TX", ?, ?)');
    }

    protected function grammarValue($driver = "mysql", $version = 0) {
        return 'grammar = "' . $driver . '" and version = ' . $version;
    }

    public function test_grammar_versions_resolve()
    {


        $grammar = ExpressionGrammar::make()
            ->mySql($this->grammarValue("mysql"))
            ->postgres($this->grammarValue('pgsql'));

        foreach(['mysql', 'pgsql'] as $driver)
            foreach(['2.0', '1.1', '1.0'] as $version)
                $grammar->grammar($driver, $this->grammarValue($driver, $version), $version);

        foreach(['mysql', 'pgsql'] as $driver) {
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '2.0'));
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '2.1'));
            $this->assertEquals($this->grammarValue($driver, "2.0"), $grammar->resolve($driver, '3.1'));

            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.1'));
            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.2'));
            $this->assertEquals($this->grammarValue($driver, "1.1"), $grammar->resolve($driver, '1.10'));

            $this->assertEquals($this->grammarValue($driver, "1.0"), $grammar->resolve($driver, '1.0'));

            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.0'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.1'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.2'));
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve($driver, '0.10'));
        }
    }

    public function test_resolve_grammar_without_parameters()
    {
        $grammar = ExpressionGrammar::make()
            ->mySql($this->grammarValue("mysql"))
            ->postgres($this->grammarValue('pgsql'));


        foreach(['mysql', 'pgsql'] as $driver) {
            $grammar->driver($driver)->version("4.10");
            $this->assertEquals($this->grammarValue($driver, "0"), $grammar->resolve());
        }
    }

}










