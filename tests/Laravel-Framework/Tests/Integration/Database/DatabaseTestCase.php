<?php

namespace Illuminate\Tests\Integration\Database;

use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Orchestra\Testbench\TestCase;

class DatabaseTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ExpressionsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
