<?php

namespace app\modules\api\v1\models\AdmissionStatus;

use app\modules\api\models\AppStatus;
use app\modules\api\models\RecordFilter;
use app\modules\api\models\AppQueries;
use app\modules\api\models\AppEnums;
use Yii;

class AdmissionStatusCrud{
    
    
    private function verifyCreateOrUpdateParams($admissionStatus){
        $errors = array();
        if(!isset($admissionStatus)){
            $errors["admission_status"] = "Admission status should not be null";
        }
        
        return $errors;
    }
    
    public function create($db, $admission, $lastStatus, $currentStatus, $userId){
        if( $currentStatus == AppStatus::initiated && $lastStatus == -1){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else if( $currentStatus == AppStatus::initiated && $lastStatus == AppStatus::denied){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        
        else if( ($currentStatus == AppStatus::accepted || $currentStatus == AppStatus::denied) && 
                $lastStatus == AppStatus::initiated){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else if( $currentStatus == AppStatus::bedAssigned && $lastStatus == AppStatus::accepted ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else if( ($currentStatus == AppStatus::patientArrived || $currentStatus == AppStatus::patientNoShow) 
                && $lastStatus == AppStatus::bedAssigned){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else if( $currentStatus == AppStatus::patientDischarged && $lastStatus == AppStatus::patientArrived ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else if( $currentStatus == AppStatus::closed && 
                ($lastStatus >= AppStatus::initiated && $lastStatus <= AppStatus::patientNoShow) ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 
                    $currentStatus, $userId);
        }
        else{
            return false;
        }
        return true;
    }
    
    public function read(RecordFilter $recordFilter){
        $data = AppQueries::getAdmissionStatuses($recordFilter->id);
        $result = array();
        foreach ($data as $value){
            $tempArray = array();
            $tempArray["user"] =  $value["first_name"] . " " . $value["last_name"];
            $tempArray["status"] =  $value["status"];
            $tempArray["created_on"] = $value["created_on"] ;
            $tempArray["user_id"] = $value["user_id"];
            $tempArray["status_text"] = AppEnums::getStatusText($value["status"]);
            array_push($result, $tempArray);
        }
        
        return $result;
    }

    public function readAll(RecordFilter $recordFilter){}

}