<?php

namespace Tests\Unit\Database\Eloquent;

use AngelSourceLabs\LaravelExpressions\ExpressionsServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;
use Tests\Unit\BaseTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Mockery as m;
use Tests\Fixtures\Point;
use Tests\Fixtures\TestModel;
use Tests\Unit\DatabaseConnections;
use Tests\Unit\MakesExpressions;
use Tests\Unit\Mocks\TestPDO;

class ExpressionAttributesTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;
    use DatabaseConnections;
    use MakesExpressions;

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

    public function hasCorrectInsertAndUpdateSql($insert, $insertWithId, $update)
    {
        $this->assertFalse($this->model->exists);
        $firstModel = true;

        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12, 34)) as $expression)
        {
            if ($this->model->exists) {
                $this->model->delete();
                $queryIndex++;
            }
            $this->model->point = $expression;
            $this->model->save();

            $this->assertStringStartsWith('insert', $this->pdo->queries[$queryIndex]);

            if ($firstModel) {
                $this->assertEquals($insert, $this->pdo->queries[$queryIndex]);
                $this->assertEquals(2, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
                $this->assertEquals([1 => "POINT(34 12)", 2 => 4236], $this->pdo->bindings[$queryIndex], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
            }
            else {
                $this->assertEquals($insertWithId, $this->pdo->queries[$queryIndex]);
                $this->assertEquals(3, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
                $this->assertEquals([1 => "POINT(34 12)", 2 => 4236, 3 => $lastModelId], $this->pdo->bindings[$queryIndex], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
            }
            $this->assertTrue($this->model->exists);
            $queryIndex++;
            $firstModel = false;
            $lastModelId = $this->model->id;
        }

        foreach ($this->makeExpressions(new Point(56, 78)) as $expression)
        {
            $this->model->point = $expression;
            $this->model->save();

            $this->assertStringStartsWith('update', $this->pdo->queries[$queryIndex]);
            $this->assertEquals($update, $this->pdo->queries[$queryIndex]);
            $this->assertEquals(3, count($this->pdo->bindings[$queryIndex]), "Incorrect number of bindings");
            $this->assertEquals([1 => "POINT(78 56)", 2 => 4236, 3 => $lastModelId], $this->pdo->bindings[$queryIndex], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
            $queryIndex++;
        }
    }

    /**
     * @environment-setup useMySqlConnection
     */
    public function test_MySqlConnection_has_correct_insert_and_update_Sql()
    {
        $insert = "insert into `test_models` (`point`) values (ST_GeomFromText(?, ?, 'axis-order=long-lat'))";
        $insertWithId = "insert into `test_models` (`point`, `id`) values (ST_GeomFromText(?, ?, 'axis-order=long-lat'), ?)";
        $update = "update `test_models` set `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?";

        $this->hasCorrectInsertAndUpdateSql($insert, $insertWithId, $update);
    }

    /**
     * @environment-setup usePostgresConnection
     */
    public function test_PostgresConnection_has_correct_insert_and_update_Sql()
    {
        $insert = 'insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id"';
        $insertWithId = 'insert into "test_models" ("point", "id") values (ST_GeomFromText(?, ?), ?) returning "id"';
        $update = 'update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?';

        $this->hasCorrectInsertAndUpdateSql($insert, $insertWithId, $update);
    }

}










