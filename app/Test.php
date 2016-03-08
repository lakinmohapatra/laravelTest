<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use filemaker_laravel\Database\Eloquent\Model;
use DB;

class Test extends Model
{
    protected $layoutName = 'Contracts_Test';
    protected $primaryKey = '_kp_ContractID';
    
    public function fm()
    {
        //return Test::test();
        //return Test::testQuery();
        //return Test::testElo();
        $test = $this->where('_kp_ContractID', '==', '*')
                     ->orderBy('ContractName', 'asc')
                     ->first();
                     
        $test->delete();
        //foreach ($test as $t) {
        //   echo  $t['GiftCardNumber'];
        //}
        //$test->ContractName = 'Test123';
        //$test->save();
        
        //$test = self::where('_kf_AgencyName', 'IDOT')
        //    ->update(['ContractName' => 'Hello']);
        
        //self::where('_kp_ContractID', '75A0420 Bag to Bike')
        //    ->delete();
            
        echo '<pre>';
        print_r($test);
        exit;
    }
}