<?php


namespace Tests\Unit\Database\Query;


use Composer\Semver\Semver;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Point;
use Tests\Unit\BaseTestCase;

class BuilderUpsertTest extends BaseTestCase
{
    public function setUp() : void
    {
        parent::setUp();
        if (! Semver::satisfies(app()->version(), "^8.0|^9.0|^10.0") ) $this->markTestSkipped("Upsert is supported by Laravel 8.x - 10.x only.  Laravel version is " . app()->version());
    }

    public function test_Upsert_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->upsert(['email' => 'user@example.com', 'point' => $expression], ['email'], ['point']);
            $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')) on duplicate key update `point` = values(`point`)", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_Upsert_multiple_rows_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->upsert([
                ['email' => 'user@example.com', 'point' => $expression],
                ['email' => 'user2@example.com', 'point' => $expression],
                ['email' => 'user3@example.com', 'point' => $expression],
                ], ['email'], ['point']);
            $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')) on duplicate key update `point` = values(`point`)", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([
                1 => 'user@example.com',
                2 => 'POINT(34 12)',
                3 => 4236,
                4 => 'user2@example.com',
                5 => 'POINT(34 12)',
                6 => 4236,
                7 => 'user3@example.com',
                8 => 'POINT(34 12)',
                9 => 4236,
            ], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}