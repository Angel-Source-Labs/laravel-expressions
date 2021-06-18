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


class BuilderSelectTest extends TestCase
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

    public function test_Select_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions("price * ? as price_with_tax", [1.0825]) as $expression)
        {
            DB::table('orders')->select($expression)->get();
            $this->assertEquals('select price * ? as price_with_tax from `orders`', $this->pdo->queries[0]);
            $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => "1.0825"], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }
}