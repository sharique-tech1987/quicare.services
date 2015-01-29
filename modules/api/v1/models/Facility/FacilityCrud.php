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
    
    public function readAll(RecordFilter $recordFilter){
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = Facility::find();
            
            Facility::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Facility::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Facility::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);

            $data = array("total_records" => $record_count, "records" => $query->all());
            $serviceResult = new ServiceResult(true, $data, $errors = array());
            return $serviceResult;
            
        } 
        else {
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $recordFilter->getErrors());
            return $serviceResult;
        }
    }
    
    public function read(RecordFilter $recordFilter, $findModel = true){
        $facility = Facility::findOne($recordFilter->id);
        if($facility !== null ){
            if($findModel){
                return $facility;
            }
            else{
                $facility_array = $facility->toArray();
                $facility_array["groups"] = $facility->groups;
                $facility_array["users"] = $facility->users;
                return $facility_array;
            }
            
        }
        else{
            throw new \Exception("Facility is not exist");
        }
    }
    
}
