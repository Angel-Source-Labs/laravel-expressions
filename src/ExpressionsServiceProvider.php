<?php


namespace AngelSourceLabs\LaravelExpressions;


use AngelSourceLabs\LaravelExpressions\Commands\Doctor;
use AngelSourceLabs\LaravelExpressions\Database\MySqlConnection;
use AngelSourceLabs\LaravelExpressions\Database\PostgresConnection;
use AngelSourceLabs\LaravelExpressions\Database\SQLiteConnection;
use AngelSourceLabs\LaravelExpressions\Database\SqlServerConnection;
use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Connection;

class ExpressionsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind("Illuminate\Database\Query\Builder", Builder::class);
    }

    public function boot()
    {
        $this->bindGrammarClasses();
        $this->bindCommands();
    }

    protected function bindGrammarClasses()
    {
        $connections = [
            'mysql' => [
                'connection' => MySqlConnection::class,
                'queryGrammar' => MySqlGrammar::class,
                'schemaGrammar' => \Illuminate\Database\Schema\Grammars\MySqlGrammar::class,
            ],
            'pgsql' => [
                'connection' => PostgresConnection::class,
                'queryGrammar' => PostgresGrammar::class,
                'schemaGrammar' => \Illuminate\Database\Schema\Grammars\PostgresGrammar::class,
            ],
            'sqlite' => [
                'connection' => SQLiteConnection::class,
                'queryGrammar' => SQLiteGrammar::class,
                'schemaGrammar' => \Illuminate\Database\Schema\Grammars\SQLiteGrammar::class,
            ],
            'sqlsrv' => [
                'connection' => SqlServerConnection::class,
                'queryGrammar' => SqlServerGrammar::class,
                'schemaGrammar' => \Illuminate\Database\Schema\Grammars\SqlServerGrammar::class,
            ],
        ];

        foreach($connections as $driver => $class) {
            Connection::resolverFor($driver, function($pdo, $database = '', $tablePrefix = '', array $config = []) use ($driver, $class) {
                $connection = new $class['connection']($pdo, $database, $tablePrefix, $config);
                $connection->setQueryGrammar(new $class['queryGrammar']);
                $connection->setSchemaGrammar(new $class['schemaGrammar']);

                return $connection;
            });
        }
    }

    protected function bindCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Doctor::class,
            ]);
        }
    }
}