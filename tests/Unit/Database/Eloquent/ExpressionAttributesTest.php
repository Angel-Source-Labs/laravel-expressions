<?php

namespace Tests\Unit\Database\Eloquent;

use Tests\Unit\BaseTestCase;
use Mockery as m;
use Tests\Fixtures\Point;
use Tests\Fixtures\TestModel;
use Tests\Unit\DatabaseConnections;

class ExpressionAttributesTest extends BaseTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;
    use DatabaseConnections;

    /**
     * @var TestModel
     */
    protected $model;

    public function setUp() : void
    {
        parent::setUp();

        $this->model = new TestModel();
    }

    public function tearDown() : void
    {
        $this->model->getConnection()->getPdo()->resetQueries();
        parent::tearDown();
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

    public function test_MySqlConnection_has_correct_insert_and_update_Sql()
    {
        $this->useMySqlConnection($this->app);
        $insert = "insert into `test_models` (`point`) values (ST_GeomFromText(?, ?, 'axis-order=long-lat'))";
        $insertWithId = "insert into `test_models` (`point`, `id`) values (ST_GeomFromText(?, ?, 'axis-order=long-lat'), ?)";
        $update = "update `test_models` set `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?";

        $this->hasCorrectInsertAndUpdateSql($insert, $insertWithId, $update);
    }

    public function test_PostgresConnection_has_correct_insert_and_update_Sql()
    {
        $this->usePostgresConnection($this->app);
        $insert = 'insert into "test_models" ("point") values (ST_GeomFromText(?, ?)) returning "id"';
        $insertWithId = 'insert into "test_models" ("point", "id") values (ST_GeomFromText(?, ?), ?) returning "id"';
        $update = 'update "test_models" set "point" = ST_GeomFromText(?, ?) where "id" = ?';

        $this->hasCorrectInsertAndUpdateSql($insert, $insertWithId, $update);
    }

}










