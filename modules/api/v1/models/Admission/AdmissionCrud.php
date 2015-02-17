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
        
        if($facility->deactivate === "T" && $facility->type === "HL"){
            throw new \Exception("Selected facility should be activated hospital");
        }
        
        if(!in_array($admission->group, $facilityGroupIds)){
            throw new \Exception("Selected group should exist in hospital");
        }
    }
    
    public function create(Admission $admission){
        $recordFilter = new RecordFilter();
        $recordFilter->id = $admission->hospital;
        $facility = FacilityCrud::read($recordFilter, true);
        $facilityGroups = $facility->groups;
        $facilityGroupIds = $this->getGroupsIds($facilityGroups);
        $this->verifyCreateOrUpdateParams($admission, $facility, $facilityGroupIds);
        
        
        $admission->transaction_number = $this->generateTransactionNumber($facility->npi);
        
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
    
    
    private function generateTransactionNumber($npi){
        $todayDate = date("mdY");
        $admission = Admission::getLastTransactionId($npi . $todayDate);
        
        if(sizeof($admission)){
            $transactionIdArray = explode("_", $admission->transaction_number)[1];
            $newTransactionCount = (int)$transactionIdArray + 1;
            $newTransactionCountPadded = sprintf("%02s", $newTransactionCount);
            return $npi . $todayDate . "_". $newTransactionCountPadded;
            
        }
        else{
            return $npi . $todayDate . "_01";
        }
        
    }
    
    
    private function getGroupsIds($facilityGroups){
        
        return array_map(function($g){return $g->id;}, $facilityGroups);
        
    }
    
    
}