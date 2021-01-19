<?php


namespace AngelSourceLabs\LaravelExpressions\Database\Query\Expression;


interface HasBindings
{
    public function getBindings() : array;
}