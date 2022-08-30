<?php


namespace AngelSourceLabs\LaravelExpressions\Database;


class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    use ResolvesBuilders;
}