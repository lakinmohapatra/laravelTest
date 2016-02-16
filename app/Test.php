<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;

class Test extends Model
{
    public function fm()
    {
        //return Test::test();
        return Test::testQuery();
        //return Test::testElo();
    }
}
