<?php

namespace app\modules\api\models;

use yii\db\Query;

class AppQueries {
    
    static function getHospitalGroupsQuery(){
        /*
         * Get distinct groups query which are taken by hospitals
         */
        
        return (new Query())->select(['hcgf.group_id'])
              ->distinct()
              ->from(['health_care_facility_group hcgf'])
              ->innerJoin(['health_care_facility hcf'], '`hcgf`.`facility_id` = `hcf`.`id`')
              ->where(['hcf.type' => 'HL']);
    }
    
}
