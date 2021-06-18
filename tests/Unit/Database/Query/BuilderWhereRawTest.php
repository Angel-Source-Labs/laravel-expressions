<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\Grammar;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Fixtures\ClassIsExpression;
use Tests\Fixtures\ClassIsExpressionHasBindings;
use Tests\Unit\MakesExpressions;
use Tests\Unit\Mocks\TestPDO;


class BuilderWhereRawTest extends TestCase
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

    public function test_WhereRaw_using_Expression()
    {
        foreach ($this->makeExpressions('price > IF(state = "TX", 200, 100)') as $expression) {
            $sql = DB::table('orders')->whereRaw($expression)->toSql();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", 200, 100)', $sql);
        }
    }

    public function test_WhereRaw_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('price > IF(state = "TX", ?, 100)', [200]) as $expression) {
            DB::table('orders')->whereRaw($expression)->get();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, 100)', $this->pdo->queries[0]);
            $this->assertEquals(1, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 200], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }

    public function test_WhereRaw_with_bindings_using_ExpressionWithBindings()
    {
        foreach ($this->makeExpressions('price > IF(state = "TX", ?, ?)', [200]) as $expression) {
            DB::table('orders')->whereRaw($expression, [100])->get();
            $this->assertEquals('select * from `orders` where price > IF(state = "TX", ?, ?)', $this->pdo->queries[0]);
            $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }

    public function test_WhereRaw_using_Expression_with_Grammar()
    {
        $driver = DB::connection()->getDriverName();

        $grammar = Grammar::make()
            ->mySql('grammar = "mysql"')
            ->postgres('grammar = "pgsql"')
            ->sqLite('grammar = "sqlite"')
            ->sqlServer('grammar = "sqlserver"');

        foreach ($this->makeExpressions($grammar) as $expression) {
            DB::table('users')->whereRaw($expression)->get();
            $this->assertEquals('select * from `users` where grammar = "' . $driver . '"', $this->pdo->queries[0]);
            $this->assertEquals(0, count($this->pdo->bindings), "Incorrect number of bindings");
        }
    }

    public function test_WhereRaw_using_ExpressionWithBindings_with_Grammar()
    {
        $driver = DB::connection()->getDriverName();

        $grammar = Grammar::make()
            ->mySql('grammar = "mysql" and price > IF(state = "TX", ?, ?)')
            ->postgres('grammar = "pgsql" and price > IF(state = "TX", ?, ?)')
            ->sqLite('grammar = "sqlite" and price > IF(state = "TX", ?, ?)')
            ->sqlServer('grammar = "sqlserver" price > IF(state = "TX", ?, ?)');

        foreach ($this->makeExpressions($grammar, [100, 200]) as $expression) {
            $sql = DB::table('users')->whereRaw($expression)->get();
            $this->assertEquals('select * from `users` where grammar = "' . $driver . '" and price > IF(state = "TX", ?, ?)', $this->pdo->queries[0]);
            $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
            $this->assertEquals([1 => 100, 2 => 200], $this->pdo->bindings[0], "Incorrect bindings");
        }
    }

    public function test_update()
    {
        DB::table('users')->update(['first_name' => 'brion']);
    }
}