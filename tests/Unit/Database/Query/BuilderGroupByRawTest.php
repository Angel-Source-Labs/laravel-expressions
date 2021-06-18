<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\MakesExpressions;
use Tests\Unit\Mocks\TestPDO;


class BuilderGroupByRawTest extends TestCase
{
    use MakesExpressions;

    /**
     * @var m\Mock | TestPDO
     */
    protected $pdo;

    protected function getPackageProviders($app)
    {
        return [ExpressionsServiceProvider::class];
    }

    public function setUp() : void
    {
        parent::setUp();
        $this->pdo = m::mock(TestPDO::class)->makePartial();
        $connection = DB::connection();
        $connection->setPdo($this->pdo);
    }

    public function test_GroupByRaw_using_Expression()
    {
        foreach ($this->makeExpressions('price > 100') as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression)
                ->toSql();
            $this->assertEquals('select `department`, `price` from `orders` group by price > 100', $sql);
        }
    }

    public function test_GroupByRaw_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('price > ?', [100]) as $expression)
        {
            DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression)
                ->get();
            $this->assertEquals('select `department`, `price` from `orders` group by price > ?', $this->pdo->queries[0]);
            $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }

    public function test_GroupByRaw_with_bindings_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('price > ?, department > ?', [100]) as $expression) {
            DB::table('orders')
                ->select('department', 'price')
                ->groupByRaw($expression, [1560])
                ->get();
            $this->assertEquals('select `department`, `price` from `orders` group by price > ?, department > ?', $this->pdo->queries[0]);
            $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100, 2 => 1560], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }
}