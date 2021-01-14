<?php


namespace Tests\Unit\Mocks;


use PDO;
use Mockery as m;

class TestPDO extends PDO
{
    public $queries = [];
    public $bindings = [];

    public $counter = 1;

    public function prepare($statement, $driver_options = [])
    {
        $this->queries[] = $statement;
        $key = array_key_last($this->queries);
        $bindings = &$this->bindings;

        $stmt = m::mock('PDOStatement');
        $stmt->shouldReceive('bindValue')->zeroOrMoreTimes()->withArgs(function($param, $value, $type = null)
        use (&$bindings, $key)
        {
            $bindings[$key][$param] = $value;

            return true;
        });
        $stmt->shouldReceive('execute');
        $stmt->shouldReceive('fetchAll')->andReturn([['id' => 1, 'point' => 'POINT(1 2)']]);
        $stmt->shouldReceive('rowCount')->andReturn(1);

        return $stmt;
    }

    public function lastInsertId($name = null)
    {
        return $this->counter++;
    }

    public function resetQueries()
    {
        $this->queries = [];
    }
}