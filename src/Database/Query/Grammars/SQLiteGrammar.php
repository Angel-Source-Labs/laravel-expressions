<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\UsesExpressions;

class SQLiteGrammar extends \Illuminate\Database\Query\Grammars\SQLiteGrammar
{
    use UsesExpressions, HasParameterExpressionsWithGrammar;
}
