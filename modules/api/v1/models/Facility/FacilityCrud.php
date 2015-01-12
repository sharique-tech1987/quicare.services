<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use Yii;
use app\modules\api\v1\models\FacilityGroup\FacilityGroup;
use yii\helpers\Json;

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
    
    private function addFilters($query, $filters){
        if(isset($filters))
        {
            $filter_object = Json::decode($filters, true);
            if(isset($filter_object['search_text'])){
                // Use query builder expressions for performance improvement
                
                $query->where("name LIKE :name", 
                        [":name" => "%{$filter_object['search_text']}%"]);
            }
        }
    }
    
    private function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
    }
    
    private function addOrderBy($query, $orderby, $sort){
        if(isset($orderby) && isset($sort)){
            $orderby_exp = $orderby . " " . $sort;
            $query->orderBy($orderby_exp);
        }
    }
    
    
    public function read(RecordFilter $recordFilter){
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = Facility::find();
            
            $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);

            $this->addFilters($query, $recordFilter->filter);

            $record_count = $query->count();

            $data = array("total_records" => $record_count, "records" => $query->all());
            $serviceResult = new ServiceResult(true, $data, $errors = array());
            return $serviceResult;
            
        } 
        else {
            $serviceResult = new ServiceResult(false, $data = array(), $errors = $recordFilter->getErrors());
            return $serviceResult;
        }
    }
    
}
