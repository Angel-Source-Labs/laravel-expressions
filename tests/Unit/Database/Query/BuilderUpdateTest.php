<?php


namespace Tests\Unit\Database\Query;


use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Point;
use Tests\Unit\BaseTestCase;


class BuilderUpdateTest extends BaseTestCase
{
    public function test_Update_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->where('id', 1)->update(['email' => 'user@example.com', 'point' => $expression]);
            $this->assertEquals("update `points` set `email` = ?, `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236, 4 => 1], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_UpdateOrInsert_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ([true, false] as $exists) {
            $this->pdo->mockExists($exists);
            foreach ($this->makeExpressions(new Point(12, 34)) as $expression) {
                DB::table('points')->updateOrInsert(['email' => 'user@example.com'], ['point' => $expression]);

                $this->assertEquals("select exists(select * from `points` where (`email` = ?)) as `exists`", $this->pdo->queries[$queryIndex]);
                $this->assertEquals([1 => 'user@example.com'], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                $queryIndex++;

                if ($exists) {
                    $this->assertEquals("update `points` set `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where (`email` = ?) limit 1", $this->pdo->queries[$queryIndex]);
                    $this->assertEquals([1 => 'POINT(34 12)', 2 => 4236, 3 => 'user@example.com'], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                }
                else {
                    $this->assertEquals("insert into `points` (`email`, `point`) values (?, ST_GeomFromText(?, ?, 'axis-order=long-lat'))", $this->pdo->queries[$queryIndex]);
                    $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
                }
                $queryIndex++;
            }
        }
    }

    public function test_Update_json_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->where('id', 1)->update(['email' => 'user@example.com', 'points->point' => $expression]);
            $this->assertEquals("update `points` set `email` = ?, `points` = json_set(`points`, '$.\"point\"', ST_GeomFromText(?, ?, 'axis-order=long-lat')) where `id` = ?", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236, 4 => 1], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_Increment_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->where('id', 1)->increment('checkins', 1, ['email' => 'user@example.com', 'point' => $expression]);
            $this->assertEquals("update `points` set `checkins` = `checkins` + 1, `email` = ?, `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236, 4 => 1], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

    public function test_Decrement_using_ExpressionWithBindings()
    {
        $queryIndex = 0;
        foreach ($this->makeExpressions(new Point(12,34)) as $expression)
        {
            DB::table('points')->where('id', 1)->decrement('checkins', 1, ['email' => 'user@example.com', 'point' => $expression]);
            $this->assertEquals("update `points` set `checkins` = `checkins` - 1, `email` = ?, `point` = ST_GeomFromText(?, ?, 'axis-order=long-lat') where `id` = ?", $this->pdo->queries[$queryIndex]);
            $this->assertEquals([1 => 'user@example.com', 2 => 'POINT(34 12)', 3 => 4236, 4 => 1], $this->pdo->bindings[$queryIndex], "Incorrect bindings");
            $queryIndex++;
        }
    }

}