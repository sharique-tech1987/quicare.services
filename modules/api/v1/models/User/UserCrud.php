<?php

namespace app\modules\api\v1\models\User;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\UserGroup\UserGroup;
use Yii;

use yii\helpers\Json;

class UserCrud{
    /*
     * param: User
     * param: UserGroup
     * param: UserFacility
     */
    
    public function create(User $user, $userGroups, $userFacilities){
        /*
         * $userGroups is not mandatory for all users
         * $userFacilities is not mandatory for all users
         */
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
                    $uf->user_id = $user->id;
                    $isSaved = $uf->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $uf->getErrors();
                        break;
                    }
                }

            }
//            else{
//                /*
//                 * This condition is an exception of parameter so it will check on 
//                 * top of function.
//                 */
//                $isSaved = false;
//                $errors["user_groups"] = "User groups should not be null";
//                
//            }
            
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
    
    
    public function readAll(RecordFilter $recordFilter){
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
    
    public function read(RecordFilter $recordFilter, $findModel = true){
        $facility = Facility::findOne($recordFilter->id);
        if($facility !== null ){
            if($findModel){
                return $facility;
            }
            else{
                $facility_array = $facility->toArray();
                $facility_array["groups"] = $facility->groups;
                return $facility_array;
            }
            
        }
        else{
            throw new \Exception("Facility is not exist");
        }
    }
    
}
