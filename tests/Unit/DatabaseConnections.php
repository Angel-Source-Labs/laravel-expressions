<?php


namespace Tests\Unit;


trait DatabaseConnections
{
    public function useMySqlConnection($app)
    {
        config(['database.default' => 'mysql']);
    }

    public function usePostgresConnection($app)
    {
        config(['database.default' => 'pgsql']);
    }

    protected function useSQLiteConnection($app)
    {
        config(['database.default' => 'testbench']);
        config(['database.connections.testbench' => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]]);
    }

    protected function useSqlServerConnection($app)
    {
        config(['database.default' => 'sqlsrv']);
    }
}