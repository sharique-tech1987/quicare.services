<?php

namespace app\modules\api\v1\models\AdmissionStatus;

use app\modules\api\v1\models\AdmissionStatus\AdmissionStatus;
use app\modules\api\models\AppStatus;
use app\modules\api\models\RecordFilter;
use app\modules\api\models\AppQueries;
use Yii;

class AdmissionStatusCrud{
    
    
    private function verifyCreateOrUpdateParams(AdmissionStatus $admissionStatus){
        $errors = array();
        if(!isset($admissionStatus)){
            $errors["admission_status"] = "Admission status should not be null";
        }
        
        return $errors;
    }
    
    public function create($db, $admission, $lastStatus, $currentStatus){
        if( $currentStatus == AppStatus::initiated && $lastStatus == -1){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else if( $currentStatus == AppStatus::initiated && $lastStatus == AppStatus::denied){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        
        else if( ($currentStatus == AppStatus::accepted || $currentStatus == AppStatus::denied) && 
                $lastStatus == AppStatus::initiated){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else if( $currentStatus == AppStatus::bedAssigned && $lastStatus == AppStatus::accepted ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else if( ($currentStatus == AppStatus::patientArrived || $currentStatus == AppStatus::patientNoShow) 
                && $lastStatus == AppStatus::bedAssigned){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else if( $currentStatus == AppStatus::patientDischarged && $lastStatus == AppStatus::patientArrived ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else if( $currentStatus == AppStatus::closed && 
                ($lastStatus >= AppStatus::initiated && $lastStatus <= AppStatus::patientNoShow) ){
            AppQueries::insertAdmissionStatus($db, $admission->transaction_number, $currentStatus);
        }
        else{
            return false;
        }
        return true;
    }
    
    public function read(RecordFilter $recordFilter, $findModel = true){}

    public function readAll(RecordFilter $recordFilter){}

}