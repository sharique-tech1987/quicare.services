<?php

namespace app\modules\api\models;

use yii\db\Query;

class AppQueries {
    
    
    static function findState($short_name){
        /*
         * Find state by short name
         * Params:
         * short_name : string
         * Return value: array
         */
        
        $query = new Query();
        $query->select('short_name')
        ->from('state')
        ->where('short_name = :short_name');
        
        $query->addParams([':short_name' => $short_name]);
        
        $rows = $query->one();
        return $rows;
    }
    
    static function findFacilityType($short_name){
        /*
         * Find state by short name
         * Params:
         * short_name : string
         * Return value: array
         */
        
        $query = new Query();
        $query->select('short_name')
        ->from('health_care_facility_type')
        ->where('short_name = :short_name');
        
        $query->addParams([':short_name' => $short_name]);
        
        $rows = $query->one();
        return $rows;
    }
    
    
}
