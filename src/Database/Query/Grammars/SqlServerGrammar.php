<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IdentifiesExpressions;

class SqlServerGrammar extends \Illuminate\Database\Query\Grammars\SqlServerGrammar
{
    use IdentifiesExpressions, HasParameterExpressionsWithGrammar;
}
