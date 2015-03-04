<?php

namespace app\modules\api\v1\models\Admission;

use app\modules\api\v1\models\Admission\Admission;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\Facility\FacilityCrud;
use Yii;

class AdmissionCrud{
    
    private function verifyCreateOrUpdateParams(Admission $admission, $facility, $facilityGroupIds){
        if(!isset($admission)){
            throw new \Exception("Admission should not be null");
        }
        
        if($facility->deactivate === "T" || $facility->type !== "HL"){
            throw new \Exception("Selected facility should be activated hospital");
        }
        
        if(!in_array($admission->group, $facilityGroupIds)){
            throw new \Exception("Selected group should activated and exist in hospital");
        }
    }
    
    public function create(Admission $admission){
        $recordFilter = new RecordFilter();
        $recordFilter->id = $admission->sent_to_facility;
        $facility = FacilityCrud::read($recordFilter, true);
        $facilityGroups = $facility->getActiveGroups()->all();
        $facilityGroupIds = $this->getGroupsIds($facilityGroups);
        $this->verifyCreateOrUpdateParams($admission, $facility, $facilityGroupIds);
        
        
        $admission->transaction_number = $this->generateTransactionNumber();
        
        $isSaved = $admission->save();
        $serviceResult = null;
        
        if ($isSaved) {
            $data = array("transaction_number" => $admission->transaction_number);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $admission->getErrors());
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