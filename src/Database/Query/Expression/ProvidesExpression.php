<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;

/**
 * Default implementation trait that can be used by classes implementing IsExpression
 */
trait ProvidesExpression
{
    use IdentifiesExpressions;

    /**
     * The value of the expression.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Create a new raw query expression.
     *
     * @param  mixed  $value
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

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
