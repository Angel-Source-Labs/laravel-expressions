<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Expression;

class InetAton extends Expression
{
    public function __construct($address)
    {
        parent::__construct('inet_aton(?)', [$address]);
    }
}