<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\Mocks\TestPDO;


class BuilderOrderByRawTest extends TestCase
{
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

    public function testOrderByRawUsingExpression()
    {
        $ids = [12,23,34,45];
        $expression = new Expression('field(id, 12, 23, 34, 45)');
        $sql = DB::table('orders')
            ->whereIn('id', $ids)
            ->orderByRaw($expression)
            ->toSql();
        $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, 12, 23, 34, 45)', $sql);
    }

    public function testOrderByRawUsingExpressionWithBindings()
    {
        $ids = [12,23,34,45];
        $expression = new ExpressionWithBindings('field(id, ?, ?, ?, ?)', $ids);
        DB::table('orders')
            ->whereIn('id', $ids)
            ->orderByRaw($expression)
            ->get();
        $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?)', $this->pdo->queries[0]);
        $this->assertEquals(8, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function testOrderByRawWithBindingsUsingExpressionWithBindings()
    {
        $ids = [12,23,34,45];
        $expression = new ExpressionWithBindings('field(id, ?, ?, ?, ?)', [12,23]);
        DB::table('orders')
            ->whereIn('id', $ids)
            ->orderByRaw($expression,[34,45])
            ->get();
        $this->assertEquals('select * from `orders` where `id` in (?, ?, ?, ?) order by field(id, ?, ?, ?, ?)', $this->pdo->queries[0]);
        $this->assertEquals(8, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => 12, 2 => 23, 3 => 34, 4 => 45, 5 => 12, 6 => 23, 7 => 34, 8 => 45], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function SqlUsingIsExpressionIsCorrect()
    {

    }

    public function SqlUsingIsExpressionAndHasBindingsIsCorrect()
    {

    }

}