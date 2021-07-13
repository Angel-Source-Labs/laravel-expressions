<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IdentifiesExpressions;

class SQLiteGrammar extends \Illuminate\Database\Query\Grammars\SQLiteGrammar
{
    use IdentifiesExpressions, HasParameterExpressionsWithGrammar;
}
