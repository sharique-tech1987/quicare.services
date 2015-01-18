<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\v1\models\Group\Group;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use yii\helpers\Json;

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
            
            $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);

            $this->addFilters($query, $recordFilter->filter);

            $record_count = $query->count();

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