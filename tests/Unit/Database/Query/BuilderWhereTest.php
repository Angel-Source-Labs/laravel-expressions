<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\Mocks\TestPDO;


class BuilderWhereTest extends TestCase
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

    public function testWhereWithExpressionParameter()
    {
        $expression = new ExpressionWithBindings("inet_aton(?)", ["192.168.0.1"]);
        DB::table('audits')->where('ip', $expression)->get();
        $this->assertEquals('select * from `audits` where `ip` = inet_aton(?)', $this->pdo->queries[0]);
        $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => "192.168.0.1"], $this->pdo->bindings[0], "Incorrect bindings");
    }

    // TODO more tests to be sure I didn't break where

    public function SqlUsingIsExpressionIsCorrect()
    {

    }

    public function SqlUsingIsExpressionAndHasBindingsIsCorrect()
    {

    }

}