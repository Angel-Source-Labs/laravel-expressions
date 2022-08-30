<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IdentifiesExpressions;

class MySqlGrammar extends \Illuminate\Database\Query\Grammars\MySqlGrammar
{
    use IdentifiesExpressions, HasParameterExpressionsWithGrammar;
}
