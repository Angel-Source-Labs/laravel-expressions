<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\Mocks\TestPDO;


class BuilderSelectRawTest extends TestCase
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

    public function testSelectRawUsingExpression()
    {
        $expression = new Expression("price as price_before_tax");
        $sql = DB::table('orders')->selectRaw($expression)->toSql();
        $this->assertEquals('select price as price_before_tax from `orders`', $sql);
    }

    public function testSelectRawUsingExpressionWithBindings()
    {
        $expression = new ExpressionWithBindings("price * ? as price_with_tax", [1.0825]);
        DB::table('orders')->selectRaw($expression)->get();
        $this->assertEquals('select price * ? as price_with_tax from `orders`', $this->pdo->queries[0]);
        $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => "1.0825"], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function testSelectRawWithBindingsUsingExpressionWithBindings()
    {
        $expression = new ExpressionWithBindings("price * ? as price_with_tax, price * ? as profit", [1.0825]);
        DB::table('orders')->selectRaw($expression, [.20])->get();
        $this->assertEquals('select price * ? as price_with_tax, price * ? as profit from `orders`', $this->pdo->queries[0]);
        $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => 1.0825, 2 => 0.20], $this->pdo->bindings[0], "Incorrect bindings");
    }

    public function SqlUsingIsExpressionIsCorrect()
    {

    }

    public function SqlUsingIsExpressionAndHasBindingsIsCorrect()
    {

    }

}