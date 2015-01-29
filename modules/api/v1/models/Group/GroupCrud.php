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
    
    public function readAll(RecordFilter $recordFilter){
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = Group::find();
            
            Group::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Group::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Group::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);

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