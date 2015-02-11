<?php

namespace app\modules\api\v1\models\User;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\UserGroup\UserGroup;
use Yii;
use app\modules\api\v1\models\UserFacility\UserFacility;
use app\modules\api\v1\models\User\User;

class UserCrud{
    /*
     * param: User
     * param: UserGroup
     * param: UserFacility
     */
    
    private function verifyCreateOrUpdateParams(User $user, $userGroups, $userFacilities){
        /*
         * Function checks for valid params and throws exception if it has not valid params
         * E.g. Check if user is hospital physician it has groups and facilities
         */
        $checkUserGroup = isset($userGroups);
        $checkUserFacilities = isset($userFacilities);
        
        if($checkUserGroup && !(is_array($userGroups) && !empty($userGroups)) ){
            throw new \Exception("Groups should be array");
        }
        if($checkUserFacilities && !(is_array($userFacilities) && !empty($userFacilities)) ){
            throw new \Exception("Facilities should be array");
        }
        
        
        if( (isset($user->category) && isset($user->role)) ){
            if( ( ($user->category == "HL" && $user->role == "PN") || 
                  ($user->category == "CC" && $user->role == "SN") ) && 
                !( $checkUserGroup && $checkUserFacilities)  ){
                throw new \Exception("User should have groups and facilities");
            }
//            else if( !( ($user->category == "HL" && $user->role == "PN") || 
//                  ($user->category == "CC" && $user->role == "SN") ) && 
//                $user->category != "AS" && $user->category != "HR"  ){
            else if( !( ($user->category == "HL" && $user->role == "PN") || 
                  ($user->category == "CC" && $user->role == "SN") || 
                  ($user->category == "HR") || 
                ($user->category == "AS" &&  in_array($user->role, array("SR", "AR"))) ) ){
                
                if(!$checkUserFacilities){
                    throw new \Exception("User should have facilities");
                }
                else if($checkUserGroup){
                    throw new \Exception("User should not have groups");
                }
                
            }
//            else if( ($user->category == "AS" || $user->category == "HR") 
//                && ($checkUserGroup || $checkUserFacilities)  ){
            else if( ( ($user->category == "AS" &&  in_array($user->role, array("SR", "AR"))) || 
                $user->category == "HR") 
                && ($checkUserGroup || $checkUserFacilities)  ){
                throw new \Exception("User should not have groups and facilities");
            }
        }
        
    }
    
    public function create(User $user, $userGroups, $userFacilities){
        /*
         * $userGroups is not mandatory for all users
         * $userFacilities is not mandatory for all users
         */
        $this->verifyCreateOrUpdateParams($user, $userGroups, $userFacilities);
        
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = $user->save();
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if (isset($userGroups)){
                foreach ($userGroups as $ug) {
                    $ug->user_id = $user->id;
                    $isSaved = $ug->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $ug->getErrors();
                        break;
                    }
                }

            }
//          if no errors in previous operation then proceed  
            if ( (sizeof($errors) == 0) && isset($userFacilities) ){
                foreach ($userFacilities as $uf) {
                    if($user->category === "HL"){
                        $uf->scenario = "hospital";
                    }
                    else if(in_array ($user->category, array("CC", "FT", "ET")) &&
                            !($user->category === "CC" && $user->role === "SN")   ){
                        $uf->scenario = "clinic";
                    }
                    $uf->user_id = $user->id;
                    $isSaved = $uf->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $uf->getErrors();
                        break;
                    }
                }

            }

            
        }
        else {
//            Collect errors
                $errors = $user->getErrors();
        }
        
        
        $serviceResult = null;
        
        if ($isSaved) {
            $transaction->commit();
            $data = array("id" => $user->id);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $transaction->rollBack();
            $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);

        }
        
        return $serviceResult;
    }
    
    public function update(User $user, $userGroups, $userFacilities){
        /*
         * $userGroups is not mandatory for all users
         * $userFacilities is not mandatory for all users
         */
        $this->verifyCreateOrUpdateParams($user, $userGroups, $userFacilities);
        
        $transaction = Yii::$app->db->beginTransaction();
        if(strtoupper($user->deactivate) === 'T'){
           $user->enable_two_step_verification = 'F'; 
        }
        
        $isSaved = $user->save();
        UserGroup::deleteUsersGroups($user->id);
        UserFacility::deleteUsersFacilities($user->id);
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if (isset($userGroups)){
                foreach ($userGroups as $ug) {
                    $ug->user_id = $user->id;
                    $isSaved = $ug->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $ug->getErrors();
                        break;
                    }
                }

            }
//          if no errors in previous operation then proceed  
            if ( (sizeof($errors) == 0) && isset($userFacilities) ){
                foreach ($userFacilities as $uf) {
                    $uf->user_id = $user->id;
                    $isSaved = $uf->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $uf->getErrors();
                        break;
                    }
                }

            }

            
        }
        else {
//            Collect errors
                $errors = $user->getErrors();
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
    
    public function verifyReadParams($facilities){
        $checkFacilities = isset($facilities);
        
        if($checkFacilities && !is_bool($facilities) ){
            throw new \Exception("Facilities should be true of false");
        }
        
        
    }
    
    public function readAll(RecordFilter $recordFilter, $affiliatedFacilities = false){
        $this->verifyReadParams($affiliatedFacilities);
        $serviceResult = null;
        if ($recordFilter->validate()) {
            
            $query = User::find();
            
            $filteredFields;
            if (isset($recordFilter->fields)){
                $filteredFields = array_filter(explode(',', $recordFilter->fields));
            }
            else{
                $filteredFields = array();
            }
            
            if($affiliatedFacilities){
                $query->with(['facilities']);
            }
            
            
            User::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);

            User::addFilters($query, $recordFilter->filter);

            $record_count = $query->distinct()->count();
            User::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
            
            $result = $query->all();
            
            if($affiliatedFacilities){
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
            $serviceResult = new ServiceResult(false, $data = array(), $errors = $recordFilter->getErrors());
            return $serviceResult;
        }
    }
    
    public function read(RecordFilter $recordFilter, $findModel = true){
        $user = User::findOne($recordFilter->id);
        if($user !== null ){
            if($findModel){
                return $user;
            }
            else{
                $user_array = $user->toArray();
                $user_array["groups"] = $user->groups;
                $user_array["facilities"] = $user->facilities;
                return $user_array;
            }
            
        }
        else{
            throw new \Exception("User is not exist");
        }
    }
    
    private function getFacilitiesString($facilities){
        return implode(",", array_filter(array_map(function($fac){
            return $fac->name; 
        }, $facilities)) );
    }
    
}
