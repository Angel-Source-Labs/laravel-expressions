<?php


namespace AngelSourceLabs\LaravelExpressions;


use AngelSourceLabs\LaravelExpressions\Query\Grammars\MySqlGrammar;
use AngelSourceLabs\LaravelExpressions\Query\Grammars\PostgresGrammar;
use AngelSourceLabs\LaravelExpressions\Query\Grammars\SQLiteGrammar;
use AngelSourceLabs\LaravelExpressions\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;

class ExpressionsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
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

        foreach($connections as $driver => $class) {
            Connection::resolverFor($driver, function($pdo, $database = '', $tablePrefix = '', array $config = []) use ($driver, $class) {
                $connection = new $class['connection']($pdo, $database, $tablePrefix, $config);
                $connection->setQueryGrammar(new $class['grammar']);

                return $connection;
            });
        }
    }
}