<?php

namespace AngelSourceLabs\LaravelExpressions\Commands;

use AngelSourceLabs\LaravelExpressions\Database\MySqlConnection;
use AngelSourceLabs\LaravelExpressions\Database\PostgresConnection;
use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\HasBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SqlServerGrammar;
use AngelSourceLabs\LaravelExpressions\Database\SQLiteConnection;
use AngelSourceLabs\LaravelExpressions\Database\SqlServerConnection;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Assert;
use Tests\Fixtures\TestModel;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class Doctor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expressions:doctor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that Expressions are properly loaded and diagnose any problems.';

    protected $passed = 0;
    protected $failed = 0;
    protected $ignored = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->examineDatabaseManager();
        $this->examineConnection();
        $this->examineQueryBuilder();
        $this->testExpression();
        $this->testGrammar();

        return $this->results();
    }

    public function passed($passed = 1)
    {
        if ($passed) $this->passed += $passed;
    }

    public function failed($failed = 1)
    {
        if ($failed) $this->failed += $failed;
    }

    public function ignored($ignored = 1)
    {
        if ($ignored) $this->ignored += $ignored;
    }

    public function results()
    {
        $total = $this->passed + $this->ignored + $this->failed;
        $this->line("");
        $this->line("$total checks");
        $this->info("✅ $this->passed passed");
        $this->warn("⚠️ $this->ignored ignored");
        $this->error("❌ $this->failed failed");

        return $this->failed == 0;
    }


    public function reportInstanceOf($expected, $actual, $message)
    {
        if ($actual instanceof $expected) {
            $this->info("✅ " . $message . ': ' . get_class($actual));
            $this->passed();
        }
        else {
            $this->error("❌ " . $message . ': ' . get_class($actual) . '.  Expected instance of ' . $expected);
            $this->failed();
        }

        return $actual instanceof $expected;
    }

    public function reportSqlContains(string $expected, string $actual, string $message = '')
    {
        $contains = Str::contains($actual, $expected);
        if ($contains) {
            $this->info("✅ " . $message . ': ' . $actual . ' contains ' . $expected);
            $this->passed();
        }
        else {
            $this->error("❌ " . $message . ': expected ' . $actual . ' to contain ' . $expected);
            $this->failed();
        }

        return $contains;
    }

    public function reportHasBindings(array $expected, array $actual, string $message = '')
    {
        $contains = count(array_diff($expected, $actual)) == 0;
        $strActual = '[' . implode(',', $actual) . ']';
        $strExpected = '[' . implode(',', $expected) . ']';
        if ($contains) {
            $this->info("✅ " . $message . ': ' . $strActual . ' contains ' . $strExpected);
            $this->passed();
        }
        else {
            $this->error("❌ " . $message . ': expected ' . $strActual . ' to contain ' . $strExpected);
            $this->failed();
        }

        return $contains;
    }

    public function examineDatabaseManager()
    {
        $this->line("DatabaseManager");
        $this->line("---------------");
        $this->line("The DatabaseManager is used to create database Connection instances.  It is expected to be an instance of " . DatabaseManager::class);

        $databaseManager = app('db');
        $this->reportInstanceOf(DatabaseManager::class,
            $databaseManager,
            "DatabaseManager from container");

        $databaseManager = DB::getFacadeRoot();
        $this->reportInstanceOf(DatabaseManager::class,
            $databaseManager,
            "DatabaseManager from facade root");

        $this->line("");
    }

    public function examineConnection()
    {
        $connections = [
            'mysql' => [
                'connection' => MySqlConnection::class,
                'grammar' => MySqlGrammar::class,
            ],
            'pgsql' => [
                'connection' => PostgresConnection::class,
                'grammar' => PostgresGrammar::class,
            ],
            'sqlite' => [
                'connection' => SQLiteConnection::class,
                'grammar' => SQLiteGrammar::class,
            ],
            'sqlsrv' => [
                'connection' => SqlServerConnection::class,
                'grammar' => SqlServerGrammar::class,
            ],
        ];

        $this->line("Connection");
        $this->line("----------");
        $this->line("The Connection implements a connection to the database.  It is responsible for returning an " .
            "instance of the Query Builder that is used to evaluate Expressions and it is responsible for returning an " .
            "instance of the Query Grammar that is used by the Grammar helper to evaluate grammar for the driver.");

        $connection = DB::connection();
        $driver = $connection->getDriverName();
        if (!isset($connections[$driver])) {
            $this->warn("⚠️ Database driver $driver is not known.");
            $this->warn("⚠️ Connection: " . get_class($connection));
            $this->warn("⚠️ Query Grammar: " . get_class($connection->getQueryGrammar()));
            $this->ignored(2);
        }

        $this->reportInstanceOf($connections[$driver]['connection'], $connection, "Connection");
        $this->reportInstanceOf($connections[$driver]['grammar'], $connection->getQueryGrammar(), "Query Grammar");

        $this->line("");
    }

    public function examineQueryBuilder()
    {
        $this->line("Query Builder");
        $this->line("-------------");
        $this->line("The Query Builder is responsible for evaluating the Expressions and Bindings.  It is expected to be an instance of " . Builder::class . ".");

        // check query builder
        $builder = app('Illuminate\Database\Query\Builder');
        $this->reportInstanceOf(Builder::class,
            $builder,
            "Query Builder from container");

        // check query builder from DB facade
        $builder = DB::table("users");
        $this->reportInstanceOf(Builder::class,
            $builder,
            "Query Builder from DB facade");

        // check eloquent query builder
        $model = new class extends Model {};
        $builder = $model->where('id', 1)->getQuery();
        $this->reportInstanceOf(Builder::class,
            $builder,
            "Eloquent Query Builder");

        $this->line("");
    }

    public function testExpression()
    {
        $this->line("Test Expression");
        $this->line("---------------");
        
        $expression = new ExpressionWithBindings('IF(state = "TX", ?, ?)', [200, 100]);
        $query = DB::table("users")->where('price', '>', $expression);
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $this->reportSqlContains($expression->getValue(), $sql, "Test SQL using expression");
        $this->reportHasBindings([200, 100], $bindings, "Test bindings are processed");
        $this->line("");
    }

    public function testGrammar()
    {
        $this->line("Test Grammar helper");
        $this->line("-------------------");

        $driver = DB::connection()->getDriverName();

        $grammar = Grammar::make()
            ->mySql('database = "mysql"')
            ->postgres('database = "pgsql"')
            ->sqLite('database = "sqlite"')
            ->sqlServer('database = "sqlserver"');

        $expression = new ExpressionWithBindings($grammar, []);
        try {
//            $sql = DB::table('users')->where('db', $expression)->toSql();
            $sql = DB::table('users')->whereRaw($expression)->toSql();
        }
        catch(Exception $e) {
            $this->failed();
            $this->error('Exception thrown when evaluating Grammar.');
            return false;
        };

        $result = $this->reportSqlContains($driver, $sql, "Test Grammar contains $driver");
        $this->line("");
        return $result;
    }
}