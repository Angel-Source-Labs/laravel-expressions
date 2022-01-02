<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


interface IsExpression
{
    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Return getValue() as string
     * This function will typically be implemented as:
     * `return (string) $this->getValue();`
     *
     * @return string
     */
    public function __toString();

    /**
     * Return true if Expression has bindings
     *
     * @return bool
     */
    public function hasBindings() : bool;

    /**
     * Return bindings for Expression
     *
     * @return array
     */
    public function getBindings() : array;
}