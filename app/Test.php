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
        
        $test = $this->where('EmailFlag', '==', '1')
                     ->get();
        foreach ($test as $t) {
           echo  $t['GiftCardNumber'];
        }
        echo '<pre>';
        print_r($test);
        exit;
        $test->name = 'dsfds';
        $test->save();
    }
}
