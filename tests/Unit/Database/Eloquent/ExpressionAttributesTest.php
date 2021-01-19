<?php

namespace Tests\Unit\Database\Eloquent;

use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Tests\Unit\BaseTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Mockery as m;
use Tests\Models\Point;
use Tests\Models\TestModel;
use Tests\Unit\DatabaseConnections;
use Tests\Unit\Mocks\TestPDO;

class ExpressionAttributesTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;
    use DatabaseConnections;

    /**
     * @var TestModel
     */
    protected $model;

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

        $this->model = new TestModel();
    }

    public function tearDown() : void
    {
        $this->model->getConnection()->getPdo()->resetQueries();
    }

    public function hasCorrectInsertAndUpdateSql($insert, $update)
    {
        $this->assertFalse($this->model->exists);

        $this->model->point = new Point(12, 34);
        $this->model->save();

        $this->assertStringStartsWith('insert', $this->pdo->queries[0]);
        $this->assertEquals($insert, $this->pdo->queries[0]);
        $this->assertEquals(2, count($this->pdo->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => "POINT(34 12)", 2 => 4236], $this->pdo->bindings[0], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
        $this->assertTrue($this->model->exists);

        $this->model->point = new Point(56, 78);
        $this->model->save();

        $this->assertStringStartsWith('update', $this->pdo->queries[1]);
        $this->assertEquals($update, $this->pdo->queries[1]);
        $this->assertEquals(3, count($this->pdo->bindings[1]), "Incorrect number of bindings");
        $this->assertEquals([1 => "POINT(78 56)", 2 => 4236, 3 => 1], $this->pdo->bindings[1], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
    }

    /**
     * @environment-setup useMySqlConnection
     */
    public function testMySqlConnectionHasCorrectInsertAndUpdateSql()
    {
        $insert = "insert into `test_models` (`point`) values (ST_GeomFromText(?, ?, 'axis-order=long-lat'))";
        $update = "update `test_models` set `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?";

        $this->hasCorrectInsertAndUpdateSql($insert, $update);
    }

    /**
     * @environment-setup usePostgresConnection
     */
    public function testPostgresConnectionHasCorrectInsertAndUpdateSql()
    {
        $insert = 'insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id"';
        $update = 'update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?';

        $this->hasCorrectInsertAndUpdateSql($insert, $update);
    }

}










