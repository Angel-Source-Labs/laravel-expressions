<?php


namespace Tests\Unit\Database\Query;


use AngelSourceLabs\LaravelExpressions\Database\Query\Expression\ExpressionWithBindings;
use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Fixtures\InetAton;
use Tests\Unit\MakesExpressions;
use Tests\Unit\Mocks\TestPDO;


class BuilderWhereTest extends TestCase
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
    
    public function test_where_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression) {
            $query = DB::table('audits')->where('ip', $expression);
            $query->get();
            $this->assertEquals('select * from `audits` where `ip` = inet_aton(?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "192.168.0.1"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_where_using_ExpressionWithBindings_with_two_bindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('IF(state = "TX", ?, ?)', [200, 100]) as $expression) {
            DB::table('orders')->where('price', '>', $expression)->get();
            $this->assertEquals('select * from `orders` where `price` > IF(state = "TX", ?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 200, 2 => 100], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_orWhere_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.2")) as $expression2) {
                DB::table('audits')
                    ->where('ip', $expression1)
                    ->orWhere('ip', $expression2)
                    ->get();
                $this->assertEquals('select * from `audits` where `ip` = inet_aton(?) or `ip` = inet_aton(?)', $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.2"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;
            }
        }
    }

    public function test_whereBetween_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.2")) as $expression2) {
                DB::table('audits')
                    ->whereBetween('ip', [new InetAton("192.168.0.1"), new InetAton("192.168.0.100")])
                    ->get();
                $this->assertEquals('select * from `audits` where `ip` between inet_aton(?) and inet_aton(?)', $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.100"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;
            }
        }
    }

    public function test_orWhereBetween_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.2")) as $expression2) {
                DB::table('audits')
                    ->whereBetween('ip', [new InetAton("192.168.0.1"), new InetAton("192.168.0.100")])
                    ->orWhereBetween('ip', [new InetAton("172.22.0.1"), new InetAton("172.22.0.100")])
                    ->get();
                $this->assertEquals('select * from `audits` where `ip` between inet_aton(?) and inet_aton(?) or `ip` between inet_aton(?) and inet_aton(?)', $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.100", 3 => "172.22.0.1", 4 => "172.22.0.100"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;
            }
        }
    }

    public function test_whereNotBetween_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.2")) as $expression2) {
                DB::table('audits')
                    ->whereNotBetween('ip', [new InetAton("192.168.0.1"), new InetAton("192.168.0.100")])
                    ->get();
                $this->assertEquals('select * from `audits` where `ip` not between inet_aton(?) and inet_aton(?)', $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.100"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;
            }
        }
    }

    public function test_orWhereNotBetween_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.2")) as $expression2) {
                DB::table('audits')
                    ->whereNotBetween('ip', [new InetAton("192.168.0.1"), new InetAton("192.168.0.100")])
                    ->orWhereNotBetween('ip', [new InetAton("172.22.0.1"), new InetAton("172.22.0.100")])
                    ->get();
                $this->assertEquals('select * from `audits` where `ip` not between inet_aton(?) and inet_aton(?) or `ip` not between inet_aton(?) and inet_aton(?)', $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.100", 3 => "172.22.0.1", 4 => "172.22.0.100"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;
            }
        }
    }

    public function test_whereIn_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.3")) as $expression2) {
                foreach ($this->makeExpressions(new InetAton("192.168.0.5")) as $expression3) {
                    DB::table('audits')
                        ->whereIn('ip', [
                            $expression1,
                            $expression2,
                            $expression3,
                        ])
                        ->get();
                    $this->assertEquals('select * from `audits` where `ip` in (inet_aton(?), inet_aton(?), inet_aton(?))', $this->pdo->queries[$queryIndex]);
                    $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.3", 3 => "192.168.0.5"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                    $queryIndex++;
                }
            }
        }
    }

    public function test_whereNotIn_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.3")) as $expression2) {
                foreach ($this->makeExpressions(new InetAton("192.168.0.5")) as $expression3) {
                    DB::table('audits')
                        ->whereNotIn('ip', [
                            $expression1,
                            $expression2,
                            $expression3,
                        ])
                        ->get();
                    $this->assertEquals('select * from `audits` where `ip` not in (inet_aton(?), inet_aton(?), inet_aton(?))', $this->pdo->queries[$queryIndex]);
                    $this->assertEquals([1 => "192.168.0.1", 2 => "192.168.0.3", 3 => "192.168.0.5"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                    $queryIndex++;
                }
            }
        }
    }

    public function test_orWhereIn_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.3")) as $expression2) {
                foreach ($this->makeExpressions(new InetAton("172.22.0.1")) as $expression3) {
                    foreach ($this->makeExpressions(new InetAton("172.22.0.3")) as $expression4) {
                        DB::table('audits')
                            ->whereIn('ip', [
                                $expression1,
                                $expression2,
                                new InetAton("192.168.0.5"),
                            ])
                            ->orWhereIn('ip', [
                                $expression3,
                                $expression4,
                                new InetAton("172.22.0.5"),
                            ])
                            ->get();
                        $this->assertEquals('select * from `audits` where `ip` in (inet_aton(?), inet_aton(?), inet_aton(?)) or `ip` in (inet_aton(?), inet_aton(?), inet_aton(?))', $this->pdo->queries[$queryIndex]);
                        $this->assertEquals([
                            1 => "192.168.0.1",
                            2 => "192.168.0.3",
                            3 => "192.168.0.5",
                            4 => "172.22.0.1",
                            5 => "172.22.0.3",
                            6 => "172.22.0.5"
                        ], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                        $queryIndex++;
                    }
                }
            }
        }
    }

    public function test_orWhereNotIn_using_ExpressionWithBindings_specialized_subclass()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new InetAton("192.168.0.1")) as $expression1) {
            foreach ($this->makeExpressions(new InetAton("192.168.0.3")) as $expression2) {
                foreach ($this->makeExpressions(new InetAton("172.22.0.1")) as $expression3) {
                    foreach ($this->makeExpressions(new InetAton("172.22.0.3")) as $expression4) {
                        DB::table('audits')
                            ->whereNotIn('inbound', [
                                $expression1,
                                $expression2,
                                new InetAton("192.168.0.5"),
                            ])
                            ->orWhereNotIn('outbound', [
                                $expression3,
                                $expression4,
                                new InetAton("172.22.0.5"),
                            ])
                            ->get();
                        $this->assertEquals('select * from `audits` where `inbound` not in (inet_aton(?), inet_aton(?), inet_aton(?)) or `outbound` not in (inet_aton(?), inet_aton(?), inet_aton(?))', $this->pdo->queries[$queryIndex]);
                        $this->assertEquals([
                            1 => "192.168.0.1",
                            2 => "192.168.0.3",
                            3 => "192.168.0.5",
                            4 => "172.22.0.1",
                            5 => "172.22.0.3",
                            6 => "172.22.0.5"
                        ], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                        $queryIndex++;
                    }
                }
            }
        }
    }

    public function test_whereDate_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('DATE_ADD(?, ?)', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereDate('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where date(`created_at`) = DATE_ADD(?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereMonth_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('MONTH(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereMonth('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where month(`created_at`) = MONTH(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereDay_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('DAY(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereDay('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where day(`created_at`) = DAY(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereYear_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('YEAR(DATE_ADD(?, ?))', ['2016-12-31', 10]) as $expression) {
            DB::table('audits')
                ->whereYear('created_at', $expression)
                ->get();
            $this->assertEquals('select * from `audits` where year(`created_at`) = YEAR(DATE_ADD(?, ?))', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "2016-12-31", 2 => 10], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_whereTime_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions('ADDTIME(?, ?)', ['11:20:45', "10:00"]) as $expression) {
            DB::table('audits')
                ->whereTime('created_at', new ExpressionWithBindings('ADDTIME(?, ?)', ['11:20:45', "10:00"]))
                ->get();
            $this->assertEquals('select * from `audits` where time(`created_at`) = ADDTIME(?, ?)', $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => "11:20:45", 2 => "10:00"], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}