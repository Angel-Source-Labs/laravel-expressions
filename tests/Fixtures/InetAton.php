<?php


namespace Tests\Fixtures;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;

class InetAton extends ExpressionWithBindings
{
    public function __construct($address)
    {
        parent::__construct('inet_aton(?)', [$address]);
    }
}