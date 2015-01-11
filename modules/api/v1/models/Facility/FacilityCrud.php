<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use Yii;
use app\modules\api\v1\models\FacilityGroup\FacilityGroup;

class FacilityCrud{
    
    public function create($facility, $facilityGroups){
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = $facility->save();
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if (isset($facilityGroups)){
                if (is_array($facilityGroups)){
                    foreach ($facilityGroups as $fg) {
                        $fg->facility_id = $facility->id;
                        $isSaved = $fg->save();
                        if(!$isSaved){
//                        Collect Errors
                            $errors = $fg->getErrors();
                            break;
                        }
                    }
                }
                else{
                    $facilityGroups->facility_id = $facility->id;
                    $isSaved = $facilityGroups->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $facilityGroups->getErrors();
                    }
                    
                }
            }
            else{
                // Facility Groups is not set
                $isSaved = false;
                $errors["facility_groups"] = "Facility groups should not be null";
                
            }
            
        }
        else {
//            Collect errors
                $errors = $facility->getErrors();
        }
        
        
        $serviceResult = null;
        
        if ($isSaved) {
            $transaction->commit();
            $data = array("id" => $facility->id);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $transaction->rollBack();
            $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);

        }
        
        return $serviceResult;
    }
    
    public function update($facility, $facilityGroups){
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = $facility->save();
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if (isset($facilityGroups)){
                FacilityGroup::deleteFacilityGroups($facility->id);
                if (is_array($facilityGroups)){
                    foreach ($facilityGroups as $fg) {
                        $fg->facility_id = $facility->id;
                        $isSaved = $fg->save();
                        if(!$isSaved){
//                        Collect Errors
                            $errors = $fg->getErrors();
                            break;
                        }
                    }
                }
                else{
                    $facilityGroups->facility_id = $facility->id;
                    $isSaved = $facilityGroups->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $facilityGroups->getErrors();
                    }
                    
                }
            }
        }
        else {
//            Collect errors
                $errors = $facility->getErrors();
        }
        
        
        $serviceResult = null;
        
        if ($isSaved) {
            $transaction->commit();
            $data = array("message" => "Record has been updated");
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $transaction->rollBack();
            $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);

        }
        
        return $serviceResult;
    }
    
    public function read($id=null, $params=null){
        if (isset($id)) {
            if (($this->facility = Facility::findOne($id)) !== null) {
                $this->serviceResult->attributes = array('success'=>true, 
                                                    'data'=>array($this->facility->attributes), 
                                                    'error_lst'=>array());
                return $this->serviceResult;
            }
            else {
                $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>array("record" => "Could not find record"));
                return $this->serviceResult;
                
            }
            
        }
        else{
            $recordFilter = new RecordFilter();
            $recordFilter->attributes = $params;
            
            if($recordFilter->validate()){
                $this->serviceResult->attributes = $this->facility->read($recordFilter);
                return $this->serviceResult;
            }
            else{
                $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>$recordFilter->getErrors());
                return $this->serviceResult;

            }
            
        }
        
        
    }
    
}
