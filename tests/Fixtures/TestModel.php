<?php


namespace Tests\Fixtures;


use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    public $timestamps = false;

    public function testrelatedmodels()
    {
        return $this->hasMany(TestRelatedModel::class);
    }

    public function testrelatedmodels2()
    {
        return $this->belongsToMany(TestRelatedModel::class);
    }
}