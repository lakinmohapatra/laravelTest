<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;

class Test extends Model
{
    protected $layoutName = 'Test';
    
    public function fm()
    {
        //return Test::test();
        //return Test::testQuery();
        //return Test::testElo();
        
        $test = Test::where('name', '==', '*')->get('name');
        echo '<pre>';
        print_r($test);
        exit;
        $test->name = 'dsfds';
        $test->save();
    }
}
