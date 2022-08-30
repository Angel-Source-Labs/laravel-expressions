<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IdentifiesExpressions;

class PostgresGrammar extends \Illuminate\Database\Query\Grammars\PostgresGrammar
{
    use IdentifiesExpressions, HasParameterExpressionsWithGrammar;
}
