<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;

class Test extends Model
{
    protected $layoutName = 'Contracts_Test';
    
    public function fm()
    {
        //return Test::test();
        //return Test::testQuery();
        //return Test::testElo();
        $test = $this->where('_kf_ContractSerNum', '==', '1', 'or')
                     ->where('_kf_ContractSerNum', '==', '*')
                     ->orderBy('ContractName', 'asc')
                     ->skip(10)
                     ->take(5)
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
