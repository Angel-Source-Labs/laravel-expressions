<?php

namespace Tests\Unit;

use AngelSourceLabs\LaravelExpressions\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Mockery;
use Orchestra\Testbench\TestCase;
use TypeError;

class MergeExpressionBindingsTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = Mockery::mock(ConnectionInterface::class);
        $grammar = Mockery::mock(Grammar::class);
        $processor = Mockery::mock(Processor::class);

        $this->builder = new Builder($connection, $grammar, $processor);
    }

    public function test_mergeExpressionBindings_handles_string_bindings()
    {
        // We use Reflection to access the protected method
        $reflection = new \ReflectionMethod(Builder::class, 'mergeExpressionBindings');
        $reflection->setAccessible(true);

        // This should not throw TypeError anymore
        $result = $reflection->invoke($this->builder, 'some sql', 'some string binding');
        $this->assertEquals(['some string binding'], $result);
    }

    public function test_selectRaw_handles_string_bindings()
    {
        $this->builder->selectRaw('some sql', 'some string binding');
        $this->assertEquals(['some string binding'], $this->builder->getBindings());
    }

    public function test_havingRaw_handles_string_bindings()
    {
        $this->builder->havingRaw('some sql', 'some string binding');
        $this->assertEquals(['some string binding'], $this->builder->getRawBindings()['having']);
    }

    public function test_groupByRaw_handles_string_bindings()
    {
        $this->builder->groupByRaw('some sql', 'some string binding');
        // GroupBy bindings in Laravel are often merged into where or other bindings depending on implementation, 
        // but here it seems they are being added to the general bindings.
        $this->assertContains('some string binding', $this->builder->getBindings());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
