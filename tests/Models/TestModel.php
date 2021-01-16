<?php


namespace Tests\Models;


use AngelSourceLabs\LaravelExpressions\Eloquent\HasExpressionAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Mockery as m;
use Tests\Unit\Mocks\TestPDO;

class TestModel extends Model
{
    use HasExpressionAttributes;

//    protected $spatialFields = ['point'];   // TODO: only required when fetching, not saving

    public $timestamps = false;

    public static $pdo;

    public static function resolveConnection($connection = null)
    {
        if (is_null(static::$pdo)) {
            static::$pdo = m::mock(TestPDO::class)->makePartial();
        }

        return new MysqlConnection(static::$pdo);
    }

    public function testrelatedmodels()
    {
        return $this->hasMany(TestRelatedModel::class);
    }

    public function testrelatedmodels2()
    {
        return $this->belongsToMany(TestRelatedModel::class);
    }
}