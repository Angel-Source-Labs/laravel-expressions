<?php


namespace Tests\Unit\Grammars;


use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Tests\Unit\DatabaseConnections;

/**
 * Class GrammarTest
 * @package Tests\Unit\Grammars
 *
 * Tests that the ExpressionServiceProvider correctly configures:
 * 1. the QueryGrammar classes to be resolved by the DatabaseManager
 * 2. the QueryBuilder class to be resolved by the connection from the service container
 */
class ExpressionServiceProviderTest extends \Orchestra\Testbench\TestCase
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

    /**
     * @environment-setup useMySqlConnection
     */
    public function testItLoadsMySqlGrammar()
    {
        $this->assertInstanceOf(MySqlGrammar::class, DB::connection()->getQueryGrammar());
    }

    /**
     * @environment-setup usePostgresConnection
     */
    public function testItLoadsPostgresGrammar()
    {
        $this->assertInstanceOf(PostgresGrammar::class, DB::connection()->getQueryGrammar());
    }

    /**
     * @environment-setup useSQLiteConnection
     */
    public function testItLoadsSQLiteGrammar()
    {
        $this->assertInstanceOf(SQLiteGrammar::class, DB::connection()->getQueryGrammar());
    }

    /**
     * @environment-setup useSqlServerConnection
     */
    public function testItLoadsSqlServerGrammar()
    {
        $this->assertInstanceOf(SqlServerGrammar::class, DB::connection()->getQueryGrammar());
    }
}