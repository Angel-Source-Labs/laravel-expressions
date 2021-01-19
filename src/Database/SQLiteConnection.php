<?php


namespace AngelSourceLabs\LaravelExpressions\Database;


class SQLiteConnection extends \Illuminate\Database\SQLiteConnection
{
    use ResolvesQueryBuilder;
}