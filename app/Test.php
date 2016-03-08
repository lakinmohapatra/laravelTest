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
        $test = $this->where('_kp_ContractID', '==', 'S06834267-Closed Contract')
                     ->orderBy('ContractName', 'asc')
                     ->first();
                     
        //$test->delete();
        //foreach ($test as $t) {
        //   echo  $t['GiftCardNumber'];
        //}
        $test->Country = 'TestCountry';
        $test->ContractName = 'TestContract';
        $message = $test->save();
        
        //$test = self::where('_kf_AgencyName', 'IDOT')
        //    ->update(['ContractName' => 'Hello']);
        
        //self::where('_kp_ContractID', '75A0420 Bag to Bike')
        //    ->delete();
            
        echo $message;
    }
    
    public function get() {
        $test = $this->where('_kp_ContractID', '==', 'S06834267-Closed Contract')
                     ->orderBy('ContractName', 'asc')
                     ->first();
                     
        echo '<pre>';
        print_r($test);
        exit;
    }
}