<?php


namespace Tests\Unit;


trait DatabaseConnections
{
    public function useMySqlConnection($app)
    {
        config(['database.default' => 'mysql']);
        \DB::purge();
        \DB::reconnect();
        \DB::connection()->setPdo($this->pdo);
    }

    public function usePostgresConnection($app)
    {
        config(['database.default' => 'pgsql']);
        \DB::purge();
        \DB::reconnect();
        \DB::connection()->setPdo($this->pdo);
    }

    protected function useSQLiteConnection($app)
    {
        config(['database.default' => 'testbench']);
        config(['database.connections.testbench' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);
        \DB::purge();
        \DB::reconnect();
        \DB::connection()->setPdo($this->pdo);
    }

    protected function useSqlServerConnection($app)
    {
        config(['database.default' => 'sqlsrv']);
        \DB::purge();
        \DB::reconnect();
        \DB::connection()->setPdo($this->pdo);
    }
}