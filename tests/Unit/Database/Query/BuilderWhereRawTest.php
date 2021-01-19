<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\Mocks\TestPDO;


class BuilderWhereRawTest extends TestCase
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

    public function testWhereRawUsingExpression()
    {
        $expression = new Expression('price > IF(state = "TX", 200, 100)');
        $sql = DB::table('orders')->whereRaw($expression)->toSql();
        $this->assertEquals('select * from `orders` where price > IF(state = "TX", 200, 100)', $sql);
    }

    public function testWhereRawUsingExpressionWithBindings()
    {
        $expression = new ExpressionWithBindings('price > IF(state = "TX", ?, 100)', [200]);
        DB::table('orders')->whereRaw($expression)->get();
        $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, 100)', $this->pdo->queries[0]);
        $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => 200], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function testWhereRawWithBindingsUsingExpressionWithBindings()
    {
        $expression = new ExpressionWithBindings('price > IF(state = "TX", ?, ?)', [200]);
        DB::table('orders')->whereRaw($expression, [100])->get();
        $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, ?)', $this->pdo->queries[0]);
        $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function SqlUsingIsExpressionIsCorrect()
    {

    }

    public function SqlUsingIsExpressionAndHasBindingsIsCorrect()
    {

    }

}