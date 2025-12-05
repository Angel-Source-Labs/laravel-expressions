<?php


namespace Tests\Unit\Grammars;


use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Tests\Unit\BaseTestCase;
use Tests\Unit\DatabaseConnections;

/**
 * Class GrammarTest
 * @package Tests\Unit\Grammars
 *
 * Tests that the ExpressionServiceProvider correctly configures:
 * 1. the QueryGrammar classes to be resolved by the DatabaseManager
 * 2. the QueryBuilder class to be resolved by the connection from the service container
 */
class ExpressionServiceProviderTest extends BaseTestCase
{
    use DatabaseConnections;

    protected function getPackageProviders($app)
    {
        return [ExpressionsServiceProvider::class];
    }

    public function testItResolvesQueryBuilder()
    {
        $this->assertInstanceOf(Builder::class, DB::connection()->query());
    }

    public function testItLoadsMySqlGrammar()
    {
        $this->useMySqlConnection($this->app);
        $this->assertInstanceOf(MySqlGrammar::class, DB::connection()->getQueryGrammar());
    }

    public function testItLoadsPostgresGrammar()
    {
        $this->usePostgresConnection($this->app);
        $this->assertInstanceOf(PostgresGrammar::class, DB::connection()->getQueryGrammar());
    }

    public function testItLoadsSQLiteGrammar()
    {
        $this->useSQLiteConnection($this->app);
        $this->assertInstanceOf(SQLiteGrammar::class, DB::connection()->getQueryGrammar());
    }

    public function testItLoadsSqlServerGrammar()
    {
        $this->useSqlServerConnection($this->app);
        $this->assertInstanceOf(SqlServerGrammar::class, DB::connection()->getQueryGrammar());
    }
}