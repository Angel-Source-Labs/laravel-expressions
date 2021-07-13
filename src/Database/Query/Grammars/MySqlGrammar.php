<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\UsesExpressions;

class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    use UsesExpressions, HasParameterExpressionsWithGrammar;
}
