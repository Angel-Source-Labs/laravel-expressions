<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


use AngelSourceLabs\LaravelExpressions\Exceptions\GrammarNotDefinedForDatabaseException;
use Illuminate\Database\Query\Expression;

class Grammar
{
    protected $value;
    protected $driver;

    public static function make()
    {
        return new Grammar;
    }

    public function driver($driver = null)
    {
        if (func_num_args() == 0) return $this->driver;

        $this->driver = $driver;
        return $this;
    }

    public function mySql($string)
    {
        $this->value['mysql'] = $string;
        return $this;
    }

    public function postgres($string)
    {
        $this->value['pgsql'] = $string;
        return $this;
    }

    public function sqLite($string)
    {
        $this->value['sqlite'] = $string;
        return $this;
    }

    public function sqlServer($string)
    {
        $this->value['sqlsrv'] = $string;
        return $this;
    }

    public function grammar($driver, $string)
    {
        $this->value[$driver] = $string;
        return $this;
    }

    public function __invoke($driver = null)
    {
        return $this->resolve($driver);
    }

    public function resolve($driver = null)
    {
        $driver = $driver ?? $this->driver;
        $driverMsg = $driver ?? "null";
        if (! isset($this->value[$driver])) throw new GrammarNotDefinedForDatabaseException("Grammar not defined for database driver {$driverMsg}\n" . print_r($this->value, true) );
        return $this->value[$driver];
    }

    public function expression($driver = null)
    {
        return new Expression($this->resolve($driver));
    }

    public function __toString()
    {
        return $this->resolve();
    }

}