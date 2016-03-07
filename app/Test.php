<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;
use DB;

class Test extends Model
{
    protected $layoutName = 'Contracts_Test';
    
    public function fm()
    {
        //return Test::test();
        //return Test::testQuery();
        //return Test::testElo();
        $test = $this->where('_kp_ContractID', '==', '11111')
                     ->orderBy('ContractName', 'asc')
                     ->first();
                     
        
        //foreach ($test as $t) {
        //   echo  $t['GiftCardNumber'];
        //}
        $test->ContractName = 'Test123';
        $test->save();
        
        //$test = self::where('_kp_ContractID', 11111)
        //    ->update(['ContractName' => 'Test']);
            
        echo '<pre>';
        print_r($test);
        exit;
    }
}
