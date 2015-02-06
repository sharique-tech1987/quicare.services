<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\v1\models\Group\Group;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use Yii;
use app\modules\api\v1\models\UserGroup\UserGroup;
use app\modules\api\v1\models\User\User;

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
        $transaction = Yii::$app->db->beginTransaction();
        
        $isSaved = $group->save();
        $serviceResult = null;
        $errors = array();
        if ($isSaved) {
            if(strtoupper($group->deactivate) === 'T'){
                $group_users = $group->getUsers()->
                    select(["user.id", "user.deactivate", 
                        "user.enable_two_step_verification"])->all();
                $groups_user_ids = $this->getUserIdsFromUser($group_users);
                if(sizeof($groups_user_ids)){
                    $filteredUserIds = $this->getUserIdsFromUserGroup(UserGroup::filterUsersExistInMultipleGroups($groups_user_ids));
                    foreach ($group_users as $u){
                        if(in_array($u->id, $filteredUserIds)){
                            $u->deactivate = 'T';
                            $u->enable_two_step_verification = 'F';
                            $isSaved = $u->save();
                            if(!$isSaved){
    //                        Collect Errors
                                $errors = $u->getErrors();
                                break;
                            }
                        }
                    }

                }

            }
        }
        else{
//            Collect errors
                $errors = $group->getErrors();
        }
        if ($isSaved) {
            $transaction->commit();
            $data = array("message" => "Record has been updated");
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $transaction->rollBack();
            $serviceResult = new ServiceResult(false, $data = array(), $errors);
        }
        
        return $serviceResult;
    }
    
    private function getUserIdsFromUser($group_users){
        return array_map(function($users){return $users->id;}, $group_users);
    }
    
    private function getUserIdsFromUserGroup($groups_user_ids){
        
        return array_map(function($u){return $u['user_id'];}, $groups_user_ids);
        
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
                            $valueArray['facility'] = $this->getFacilitiesString($value->facilities);
                        }
                    }
                    else{
                        $valueArray['facility'] = $this->getFacilitiesString($value->facilities);
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
    
    private function getFacilitiesString($facilities){
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