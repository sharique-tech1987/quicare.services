<?php

namespace app\modules\api\v1\models\Admission;

use app\modules\api\v1\models\Admission\Admission;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\Facility\FacilityCrud;
use app\modules\api\models\AppEnums;
use Yii;
use app\modules\api\models\AppQueries;

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
        
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        $admission->transaction_number = $this->generateTransactionNumber();
        $validate = $admission->validate();
        
        if((sizeof($errors) == 0) && $validate)
        {
//           Value of this field will be depending upon the type of user
            $admission->intiated_on = date("Y-m-d H:i:s", time());
            $isSaved = $admission->save();
            if ($isSaved) {
                AppQueries::insertAdmissionStatus($db, $admission->transaction_number, 1);
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
            $transactionIdArray = explode("-", $admission->transaction_number)[1];
            $newTransactionCount = (int)$transactionIdArray + 1;
            $newTransactionCountPadded = sprintf("%02s", $newTransactionCount);
            return $todayDate . "-". $newTransactionCountPadded;
            
        }
        else{
            return $todayDate . "-01";
        }
        
    }
    
    
    private function getGroupsIds($facilityGroups){
        
        return array_map(function($g){return $g->id;}, $facilityGroups);
        
    }
    
    public function read(RecordFilter $recordFilter, $findModel = true){
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
                $admissionArray["sent_to_facility"] = $admission->hospital;
                $admissionArray["sent_by_facility"] = $admission->clinic;
                $admissionArray["sent_by_user"] = $admission->user;
                $admissionArray["group"] = $admission->groupObject;
                $admissionArray['bed_type'] = array("id" => $admission->bed_type, 
                                                    "name" => AppEnums::getBedTypeText($admission->bed_type));
                $admissionArray['code_status'] = array("id" => $admission->code_status, 
                                                       "name" => AppEnums::getCodeSatusText($admission->code_status));
                $admissionArray['mode_of_tranportation'] = array("id" => $admission->mode_of_tranportation, 
                                                       "name" => AppEnums::getTranportationText($admission->mode_of_tranportation));
                $admissionArray['diagnosis'] = $admission->admissionDiagnosis;
                
//                $admissionArray["users"] = $admission->users;
                return $admissionArray;
            }
            
        }
        else{
            throw new \Exception("Admission is not exist");
        }
    }

    public function readAll(RecordFilter $recordFilter, $withAdmissionDiagnosis = false){
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
            
            if($withAdmissionDiagnosis){
                $query->with(['admissionDiagnosis']);
            }
            
            Admission::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Admission::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Admission::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            
            $result = $query->all();
            
            if($withAdmissionDiagnosis){
                $resultArray = array();
                foreach ($result as $value){
                    $valueArray = $value->toArray($filteredFields, $filteredFields); 
                    $valueArray['bed_type'] = array("id" => $value->bed_type, "name" => AppEnums::getBedTypeText($value->bed_type));
                    $valueArray['code_status'] = array("id" => $value->code_status, "name" => AppEnums::getCodeSatusText($value->code_status));
                    $valueArray['sent_to_facility'] =  array("id" => $value->sent_to_facility, "name" => $value->hospital["name"]);
                    $valueArray['sent_by_facility'] = array("id" => $value->sent_by_facility, "name" => $value->clinic["name"]);
                    if(sizeof($filteredFields)){
                        if(in_array('diagnosis', $filteredFields)){
                            $valueArray['diagnosis'] = $this->getDiagnosisString($value->admissionDiagnosis);
                        }
                    }
                    else{
                        $valueArray['diagnosis'] = $this->getDiagnosisString($value->admissionDiagnosis);
                    }
                    array_push($resultArray, $valueArray);
                }
                
                $result = $resultArray;

            }

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
    
    private function getDiagnosisString($diagnosis){
        return implode(",", array_filter(array_map(function($diag){
            return $diag->diagnosis_desc; 
        }, $diagnosis)) );
    }

}