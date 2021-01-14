<?php

namespace Tests\Unit\Eloquent;

use Tests\Unit\BaseTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Mockery as m;
use Tests\Models\Point;
use Tests\Models\TestModel;

class HasExpressionAttributesTest extends BaseTestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var TestModel
     */
    protected $model;

    /**
     * @var array
     */
    protected $queries;

    protected $bindings;

    public function setUp() : void
    {
        $this->model = new TestModel();
        $this->queries = &$this->model->getConnection()->getPdo()->queries;
        $this->bindings = &$this->model->getConnection()->getPdo()->bindings;
    }

    public function tearDown() : void
    {
        $this->model->getConnection()->getPdo()->resetQueries();
    }

    public function testInsertUpdateExpressionHasCorrectSql()
    {
        $this->assertFalse($this->model->exists);

        $this->model->point = new Point(12, 34);
        $this->model->save();

        $this->assertStringStartsWith('insert', $this->queries[0]);
        $this->assertEquals('insert into `test_models` (`point`) values (ST_GeomFromText(?, ?, \'axis-order=long-lat\'))', $this->queries[0]);
        $this->assertEquals(2, count($this->bindings[0]), "Incorrect number of bindings");
        $this->assertEquals([1 => "POINT(34 12)", 2 => 4236], $this->bindings[0], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
        $this->assertTrue($this->model->exists);

        $this->model->point = new Point(56, 78);
        $this->model->save();

        $this->assertStringStartsWith('update', $this->queries[1]);
        $this->assertEquals('update `test_models` set `point` = ST_GeomFromText(?, ?, \'axis-order=long-lat\') where `id` = ?', $this->queries[1]);
        $this->assertEquals(3, count($this->bindings[1]), "Incorrect number of bindings");
        $this->assertEquals([1 => "POINT(78 56)", 2 => 4236, 3 => 1], $this->bindings[1], "Incorrect bindings"); // unnamed PDO bindings are 1-indexed
    }


}










