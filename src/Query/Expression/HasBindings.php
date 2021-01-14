<?php


namespace AngelSourceLabs\LaravelExpressions\Query\Expression;


interface HasBindings
{
    public function getBindings() : array;
}