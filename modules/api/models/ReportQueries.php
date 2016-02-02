<?php

namespace app\modules\api\models;

use Yii;

use yii\db\Query;
class ReportQueries {
    
    public static function getAdmissionStatus(){
        $query = Yii::$app->db->createCommand("SELECT COUNT(  `adm`.`transaction_number` ) AS `adm_count` , 
            DATE_FORMAT(  `adm`.`created_on` ,  '%Y-%c-%d' ) AS `adm_date` ,  `adm`.`last_status` AS `adm_status`
            FROM  `admission` AS  `adm` 
            WHERE  `adm`.`created_on` 
            BETWEEN  '2015-12-07 00:00:00'
            AND  '2015-12-10 23:59:59'
            GROUP BY DATE_FORMAT(  `adm`.`created_on` ,  '%Y-%c-%d' ) ,  `adm`.`last_status`");
        $rows = $query->queryAll();
        return $rows;
    }
    

}
