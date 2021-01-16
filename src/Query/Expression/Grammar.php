<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Expression;


use AngelSourceLabs\LaravelExpressions\Exceptions\GrammarNotDefinedForDatabaseException;
use Illuminate\Database\Query\Expression;

class Grammar
{
    protected $value;

    public static function make()
    {
        return new Grammar;
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

    public function __invoke($driver)
    {
        return $this->resolve($driver);
    }

    // TODO - might name this resolveAsExpression so that I also have resolve that resolves as string
    public function resolve($driver)
    {
        if (! isset($this->value[$driver])) throw new GrammarNotDefinedForDatabaseException("Grammar not defined for database driver {$driver}\n" . print_r($this->value, true) );
        return $this->value[$driver];
    }

    public function expression($driver)
    {
        return new Expression($this->resolve($driver));
    }

}