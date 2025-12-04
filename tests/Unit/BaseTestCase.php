<?php

namespace Tests\Unit;

use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Orchestra\Testbench\TestCase;
use Tests\Unit\Mocks\TestPDO;

abstract class BaseTestCase extends TestCase
{
    use MakesExpressions;

    /**
     * @var m\Mock | TestPDO
     */
    protected $pdo;

//    protected function getPackageProviders($app)
//    {
//        return [ExpressionsServiceProvider::class];
//    }

    public function setUp() : void
    {
        parent::setUp();
        $this->pdo = m::mock(TestPDO::class)->makePartial();
        $connection = DB::connection();
        $connection->setPdo($this->pdo);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }
    
    protected function assertException($exceptionName, $exceptionMessage = '', $exceptionCode = 0)
    {
        if (method_exists(parent::class, 'expectException')) {
            parent::expectException($exceptionName);
            parent::expectExceptionMessage($exceptionMessage);
            parent::expectExceptionCode($exceptionCode);
        } else {
            $this->setExpectedException($exceptionName, $exceptionMessage, $exceptionCode);
        }
    }

    public function test_ExpressionsServiceProvider_is_provided()
    {
        if (!method_exists(app(), 'getProviders')) {
            $testName = (method_exists($this, "getName")) ? $this->getName() : $this->name();
            $this->markTestSkipped(get_class($this) . ': App container has been overridden.  App instance "' . get_class(app()) . '" does not have "getProviders" method.');
        }
        $this->assertInstanceOf(ExpressionsServiceProvider::class, head(app()->getProviders(ExpressionsServiceProvider::class)));
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $traits = trait_uses_recursive($this);
        if (! isset($traits[DatabaseConnections::class]))
            config(["database.default" => "mysql"]);
    }

//    protected function defineEnvironment($app) {
//        config(["database.default" => "mysql"]);
//    }
}
