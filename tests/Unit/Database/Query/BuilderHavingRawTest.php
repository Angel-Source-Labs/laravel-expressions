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


class BuilderHavingRawTest extends TestCase
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

    public function test_HavingRaw_using_Expression()
    {
        foreach ($this->makeExpressions('SUM(price) > 2500') as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression)
                ->toSql();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > 2500', $sql);
        }
    }

    public function test_HavingRaw_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('SUM(price) > ?', [2500]) as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression)
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ?', $this->pdo->queries[0]);
            $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 2500], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }

    public function test_HavingRaw_with_bindings_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('SUM(price) > ? and AVG(price) > ?', [2500]) as $expression)
        {
            $sql = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw($expression, [100])
                ->get();
            $this->assertEquals('select `department`, SUM(price) as total_sales from `orders` group by `department` having SUM(price) > ? and AVG(price) > ?', $this->pdo->queries[0]);
            $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 2500, 2 => 100], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }
}