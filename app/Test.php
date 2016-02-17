<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;

class Test extends Model
{
    protected $layoutName = 'Web_GCT';
    
    public function fm()
    {
        //return Test::test();
        //return Test::testQuery();
        //return Test::testElo();
        
        $test = new Test();
        $test->name = 'dsfds';
        $test->save();
    }
}
