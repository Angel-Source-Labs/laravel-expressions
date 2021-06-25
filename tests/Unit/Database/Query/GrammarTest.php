<?php

namespace Tests\Unit\Database\Query;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;
use Tests\Unit\DatabaseConnections;

class GrammarTest extends BaseTestCase
{
    use DatabaseConnections;

    public function tearDown() : void
    {
        $this->pdo->resetQueries();
    }

    public function assertGrammar($sql)
    {
        $grammar = Grammar::make()
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

    /**
     * @environment-setup useMySqlConnection
     */
    public function test_MySqlConnection_has_correct_insert_and_update_Sql()
    {
        $this->assertGrammar('select * from `users` where grammar = "mysql" and price > IF(state = "TX", ?, ?)');
    }

    /**
     * @environment-setup usePostgresConnection
     */
    public function test_PostgresConnection_has_correct_insert_and_update_Sql()
    {
        $this->assertGrammar('select * from "users" where grammar = "pgsql" and price > IF(state = "TX", ?, ?)');
    }

    /**
     * @environment-setup useSQLiteConnection
     */
    public function test_useSQLiteConnection_has_correct_insert_and_update_Sql()
    {
        $this->assertGrammar('select * from "users" where grammar = "sqlite" and price > IF(state = "TX", ?, ?)');
    }

    /**
     * @environment-setup useSqlServerConnection
     */
    public function test_useSqlServerConnection_has_correct_insert_and_update_Sql()
    {
        $this->assertGrammar('select * from [users] where grammar = "sqlserver" and price > IF(state = "TX", ?, ?)');
    }

}










