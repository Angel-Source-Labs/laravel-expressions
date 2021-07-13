<?php

namespace Orchestra\Testbench;

use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Illuminate\Foundation\Testing\Concerns\InteractsWithTime;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\Concerns\MocksApplicationServices;
use PHPUnit\Framework\TestCase as PHPUnit;

abstract class TestCase extends PHPUnit implements Contracts\TestCase
{
    use Concerns\Testing,
        InteractsWithAuthentication,
        InteractsWithConsole,
        InteractsWithContainer,
        InteractsWithDatabase,
        InteractsWithExceptionHandling,
        InteractsWithSession,
        InteractsWithTime,
        MakesHttpRequests,
        MocksApplicationServices;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->setUpTheTestEnvironment();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->tearDownTheTestEnvironment();
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        return $this->setUpTheTestEnvironmentTraits($uses);
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();
    }

    protected function getPackageProviders($app)
    {
        return [ExpressionsServiceProvider::class];
    }

    public function test_ExpressionsServiceProvider_is_provided()
    {
        if (!method_exists(app(), 'getProviders'))
            $this->markTestSkipped('App container has been overridden.  App instance "'. get_class(app()) . '" does not have "getProviders" method.');
        $this->assertInstanceOf(ExpressionsServiceProvider::class, head(app()->getProviders(ExpressionsServiceProvider::class)));
    }
}
