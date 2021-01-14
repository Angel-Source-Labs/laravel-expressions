<?php


namespace AngelSourceLabs\LaravelExpressions\Expression;


interface HasBindings
{
    public function getBindings() : array;
}