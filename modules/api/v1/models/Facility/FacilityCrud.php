<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use Yii;
use app\modules\api\v1\models\FacilityGroup\FacilityGroup;
use app\modules\api\v1\models\UserFacility\UserFacility;


class FacilityCrud{
    
    //Create verify params for create and update.
    //Facility groups should not be null.
    
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
    
//    public function update($facility, $facilityGroups){
    public function update(Facility $facility, $facilityGroups){
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = $facility->save();
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if(strtoupper($facility->deactivate) === 'T'){
                $facilityUsersToDeactive = $facility->getActiveUsers()
                    ->select(["user.id", "user.deactivate", 
                        "user.enable_two_step_verification", 
                        "user.category", "user.role"])->all();
                $usersToDeactivateIds = sizeof($facilityUsersToDeactive) > 0 ? 
                    $this->getUserIdsFromUser($facilityUsersToDeactive) : array();
                if(sizeof($usersToDeactivateIds)){
                    
                    if(strtoupper($facility->type) === "HL"){
                        $filteredUserIds = $this->getUserIdsFromUserFacility(UserFacility::filterUsersExistInMultipleHospitals($usersToDeactivateIds));
                        foreach ($facilityUsersToDeactive as $u){
                        if(in_array($u->id, $filteredUserIds) && 
                            !($u->category == "AS" && $u->role == "QT") ){
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
                        if(sizeof($errors) == 0){
                            $groupsToDeactivate = $facility->getActiveGroups()
                                ->select(["group.id", "group.deactivate" ])->all();
                            foreach ($groupsToDeactivate as $g){
                                $g->deactivate = 'T';
                                $isSaved = $g->save();
                                if(!$isSaved){
        //                        Collect Errors
                                    $errors = $g->getErrors();
                                    break;
                                }
                            }
                        }
                    }
                }
                
            }
            
            // This should be in else block
            else{
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
        }
        else {
//            Collect errors
                $errors = $facility->getErrors();
        }
        
        
        $serviceResult = null;
        
//        $transaction->rollBack();
        
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
            
            $filteredFields;
            if (isset($recordFilter->fields)){
                $filteredFields = array_filter(explode(',', $recordFilter->fields));
            }
            else{
                $filteredFields = array();
            }
            
            Facility::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            Facility::addFilters($query, $recordFilter->filter);
            
            $record_count = $query->distinct()->count();
            Facility::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            
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
    
    private function getUserIdsFromUser($facilityUsers){
        return array_map(function($user){return $user->id;}, $facilityUsers);
    }
    
    private function getUserIdsFromUserFacility($facilityUserIds){
        
        return array_map(function($u){return $u['user_id'];}, $facilityUserIds);
        
    }
}
