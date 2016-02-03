<?php

namespace app\modules\api\v1\models\Report;
use app\modules\api\models\ReportQueries;
use app\modules\api\models\AppEnums;

class ReportCrud{
    public static function readAdmissionStatus(){
        $reportsData = [];

        foreach (ReportQueries::getAdmissionStatus() as $value) {
            $tempData = [ "adm_date" => $value["adm_date"], 
                AppEnums::getStatusText($value["adm_status"]) => $value["adm_count"] ];
            for($i=1; $i<=8; $i++){
                if($value["adm_status"] != $i){
                    $tempData[AppEnums::getStatusText($i)] = "0";
                }
            }
            array_push($reportsData, $tempData);
        }
//            $reportsData = array( array( "adm_date"=> "2015-12-07", "Initiated" => 1, "Accepted" => 1, "Denied" => 0, "Bed Assigned" => 2, "Patient Arrived" =>5, "Patient No-Show" => 1, "Closed" => 0, "Discharged" => 8),
//                    array( "adm_date"=> "2015-12-08", "Initiated" => 0, "Accepted" => 3, "Denied" => 2, "Bed Assigned" => 4, "Patient Arrived" =>8, "Patient No-Show" => 2, "Closed" => 1, "Discharged" => 10), 
//                array( "adm_date"=> "2015-12-09", "Initiated" => 2, "Accepted" => 2, "Denied" => 4, "Bed Assigned" => 3, "Patient Arrived" =>9, "Patient No-Show" => 3, "Closed" => 2, "Discharged" => 6),);
        
        return $reportsData;
    }
}