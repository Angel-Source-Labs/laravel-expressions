<?php


namespace AngelSourceLabs\LaravelExpressions\Database;


class PostgresConnection extends \Illuminate\Database\PostgresConnection
{
    use ResolvesQueryBuilder;
}