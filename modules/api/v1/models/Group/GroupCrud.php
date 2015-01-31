<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\v1\models\Group\Group;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;

class GroupCrud{
    
    public function create(Group $group){
        $isSaved = $group->save();
        $serviceResult = null;
        
        if ($isSaved) {
            $data = array("id" => $group->id);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $group->getErrors());
        }
        
        return $serviceResult;
    }
    
    public function update(Group $group){
        $isSaved = $group->save();
        $serviceResult = null;
        
        if ($isSaved) {
            $data = array("message" => "Record has been updated");
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $group->getErrors());
        }
        
        return $serviceResult;
    }
    
    public function verifyReadParams($facilities){
        $checkFacilities = isset($facilities);
        
        if($checkFacilities && !is_bool($facilities) ){
            throw new \Exception("Facilities should be true of false");
        }
        
        
    }
    
    public function readAll(RecordFilter $recordFilter, $affiliatedHospital = false){
        $this->verifyReadParams($affiliatedHospital);
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = Group::find();
            
            $filteredFields;
            if (isset($recordFilter->fields)){
                $filteredFields = array_filter(explode(',', $recordFilter->fields));
            }
            else{
                $filteredFields = array();
            }
            
            if($affiliatedHospital){
                $query->with(['facilities']);
            }
            
            Group::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Group::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Group::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            
            $result = $query->all();
            
            if($affiliatedHospital){
                $resultArray = array();
                foreach ($result as $value){
                    $valueArray = $value->toArray($filteredFields, $filteredFields);
                    if(sizeof($filteredFields)){
                        if(in_array('facility', $filteredFields)){
                            $valueArray['facility'] = $this->getHospitalsString($value->facilities);
                        }
                    }
                    else{
                        $valueArray['facility'] = $this->getHospitalsString($value->facilities);
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
    
    private function getHospitalsString($facilities){
        return implode(",", array_filter(array_map(function($fac){
            return strtolower($fac->type) === 'hl' ?  $fac->name : null; 
        }, $facilities)) );
    }






    public function read(RecordFilter $recordFilter, $findModel = true){
        $group = Group::findOne($recordFilter->id);
        if($group !== null ){
            if($findModel){
                return $group;
            }
            else{
                $group_array = $group->toArray();
                $group_array["facilities"] = $group->facilities;
                $group_array["users"] = $group->users;
                return $group_array;
            }
        }
        else{
            throw new \Exception("Group is not exist");
        }   
    }
}