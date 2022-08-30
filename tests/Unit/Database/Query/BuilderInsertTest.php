<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Point;
use Tests\Unit\BaseTestCase;


class BuilderInsertTest extends BaseTestCase
{
    public function test_Insert_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->insert(['email' => 'user@example.com', 'point' => $expression]);
            $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat'))", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_Insert_multiple_rows_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->insert([
                ['email' => 'user@example.com', 'point' => $expression],
                ['email' => 'user2@example.com', 'point' => $expression],
                ['email' => 'user3@example.com', 'point' => $expression],
                ]);
            $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat'))", $this->pdo->queries[$queryIndex]);
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

    public function test_InsertOrIgnore_multiple_rows_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->insertOrIgnore([
                ['email' => 'user@example.com', 'point' => $expression],
                ['email' => 'user2@example.com', 'point' => $expression],
                ['email' => 'user3@example.com', 'point' => $expression],
            ]);
            $this->assertEquals("insert ignore into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat')), (?, ST_GeomFromText(?, ?, 'axis-order=long-lat'))", $this->pdo->queries[$queryIndex]);
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

    public function test_InsertGetId_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            $id = DB::table('points')->insertGetId(['email' => 'user@example.com', 'point' => $expression]);
            $this->assertEquals($queryIndex + 1, $id);
            $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat'))", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }
}