<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

/**
 * Default implementation trait that can be used by classes implementing IsExpression
 */
trait ProvidesExpressionValue
{
    protected $value;

    /**
     * Get the value of the expression.
     *
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Return getValue() as string
     * This function will typically be implemented as:
     * `return (string) $this->getValue();`
     *
     * @return string
     */
    public function __toString() {
        return (string) $this->getValue();
    }
}
