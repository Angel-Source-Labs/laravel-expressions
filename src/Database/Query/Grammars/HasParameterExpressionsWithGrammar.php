<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Grammars;

use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\GrammarConfigurator;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\IsExpression;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait HasParameterExpressionsWithGrammar
{
    /**
     * @var GrammarConfigurator $grammarConfigurator
     */
    protected $grammarConfigurator;

    public function setGrammarConfigurator(GrammarConfigurator $grammarConfigurator)
    {
        $this->grammarConfigurator = $grammarConfigurator;
    }

    public function parameter($value)
    {
        if (isset($this->grammarConfigurator))
            $this->grammarConfigurator->configureExpression($value);

        return parent::parameter($value);
    }

}
