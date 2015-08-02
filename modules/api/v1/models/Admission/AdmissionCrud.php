<?php

namespace app\modules\api\v1\models\Admission;

use app\modules\api\v1\models\Admission\Admission;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\Facility\FacilityCrud;
use Yii;

class AdmissionCrud{
    
    private function verifyCreateOrUpdateParams(Admission $admission, $facility, $facilityGroupIds, $admissionDiagnosis){
        $errors = array();
        if(!isset($admissionDiagnosis) || !(is_array($admissionDiagnosis) && !empty($admissionDiagnosis))){
            $errors["diagnosis"] = "You cannot leave this field blank";
        }
        if(!isset($admission)){
            $errors["admission"] = "Admission should not be null";
        }
        
        if($facility->deactivate === "T" || $facility->type !== "HL"){
            $errors["facility"] = "Selected facility should be activated hospital";
        }
        
        if(!in_array($admission->group, $facilityGroupIds)){
            $errors["group"] = "Selected group should activated and exist in hospital";
        }
        
        return $errors;
    }
    
    public function create(Admission $admission, $admissionDiagnosis){
        $recordFilter = new RecordFilter();
        $recordFilter->id = $admission->sent_to_facility;
        $facility = FacilityCrud::read($recordFilter, true);
        $facilityGroups = $facility->getActiveGroups()->all();
        $facilityGroupIds = $this->getGroupsIds($facilityGroups);
        /*
         * Create Admission diagnosis table to store diagnosis
         * Diagnosis is mandatory for creating admission
         * Diagnosis should be array which sent through service
         * Use verifyCreateOrUpdateParams method to verify admission diagnosis field
         * 
         */
        $errors = $this->verifyCreateOrUpdateParams($admission, $facility, $facilityGroupIds, $admissionDiagnosis);
        
        $transaction = Yii::$app->db->beginTransaction();
        $admission->transaction_number = $this->generateTransactionNumber();
        $validate = $admission->validate();
        
        if((sizeof($errors) == 0) && $validate)
        {
            $isSaved = $admission->save();
            if ($isSaved) {
                foreach ($admissionDiagnosis as $admDiag) {
                    $admDiag->admission_id = $admission->transaction_number;
                    $isSaved = $admDiag->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $admDiag->getErrors();
                        break;
                    }
                }
            }
            
        }
        else{
            $admissionErrors = $admission->getErrors();
            $errors = array_merge($errors,$admissionErrors);
        }
        $serviceResult = null;
        
        if ((sizeof($errors) == 0)) {
            $transaction->commit();
            $data = array("transaction_number" => $admission->transaction_number);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $transaction->rollBack();
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $errors);
        }
        
        return $serviceResult;
    }
    
    
    private function generateTransactionNumber(){
        $todayDate = date("mdY");
        $admission = Admission::getLastTransactionId($todayDate);
        
        if(sizeof($admission)){
            $transactionIdArray = explode("_", $admission->transaction_number)[1];
            $newTransactionCount = (int)$transactionIdArray + 1;
            $newTransactionCountPadded = sprintf("%02s", $newTransactionCount);
            return $todayDate . "_". $newTransactionCountPadded;
            
        }
        else{
            return $todayDate . "_01";
        }
        
    }
    
    
    private function getGroupsIds($facilityGroups){
        
        return array_map(function($g){return $g->id;}, $facilityGroups);
        
    }
    
    public static function read(RecordFilter $recordFilter, $findModel = true){
        $admission = Admission::findOne($recordFilter->id);
        if($admission !== null ){
            if($findModel){
                return $admission;
            }
            else{
                $filteredFields;
                if (isset($recordFilter->fields)){
                    $filteredFields = array_filter(explode(',', $recordFilter->fields));
                }
                else{
                    $filteredFields = array();
                }
                $admissionArray = $admission->toArray($filteredFields, $filteredFields);
                $admissionArray["sent_to_facility"] = $admission->getSentToFacility()->all();
                $admissionArray["sent_by_facility"] = $admission->getSentByFacility()->all();
                $admissionArray["sent_by_user"] = $admission->getSentByUser()->all();
                $admissionArray["group"] = $admission->getGroup()->all();
                
//                $admissionArray["users"] = $admission->users;
                return $admissionArray;
            }
            
        }
        else{
            throw new \Exception("Admission is not exist");
        }
    }

    public function readAll(RecordFilter $recordFilter){
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = Admission::find();
            
            $filteredFields;
            if (isset($recordFilter->fields)){
                $filteredFields = array_filter(explode(',', $recordFilter->fields));
            }
            else{
                $filteredFields = array();
            }
            
            Admission::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Admission::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Admission::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            
            $result = $query->all();
            
            $resultArray = array();
            foreach ($result as $value){
                $valueArray = $value->toArray($filteredFields, $filteredFields);
                array_push($resultArray, $valueArray);
            }
            
            $result = $resultArray;
            

            $data = array("total_records" => $record_count, "records" => $result);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
            return $serviceResult;
            
        } 
        else {
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $recordFilter->getErrors());
            return $serviceResult;
        }
    }

}