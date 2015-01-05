<?php

namespace app\modules\api\v1\models\Facility;

use yii\db\ActiveRecord;
use app\modules\api\models\RecordFilter;
use yii\helpers\Json;

class Facility extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'health_care_facility';
    }
 
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }
    
    /**
     * Define rules for validation
     */
    public function rules() {
        
        return [ 
            [['name'], 'required', 
                'message' => 'Please enter a unique facility name' ], 
        ];
    }


//    public function scenarios()
//    {
//        $scenarios = parent::scenarios();
//        return $scenarios;
//        
//    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Set current date in created_on and updated_on
            if ($insert){
                $this->created_on = date("Y-m-d H:i:s", time());
            }

            $this->updated_on = date("Y-m-d H:i:s", time());
            return true;
        } else {
            return false;
        }
    }
    
    public function hasValidCharacter($attribute,$params){
        /*
         * Check if given character has 'F' or 'T'
         */
        $value = strtoupper(trim($this->$attribute));
        if($value != 'F' && $value != 'T'){
            $this->addError($attribute, "Invalid entry");
        }
    }
    
    public function postFacility(){
        if ($this->save()) {
            $data = array("id" => $this->id);
                    
            return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());

        } 
        else{
            $error_list = $this->populateErrors();
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>$error_list);
        }
    }
    
    private function populateErrors(){
        $error_list = array();
        foreach($this->errors as $key => $value){
            array_push($error_list, $value[0]);
        }
        return $error_list;
    }
    
    public function putFacility(){
        if ($this->save()) {
            $data = array("message" => "Record has been updated");
	 
			return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
	 
		} 
		else{
            $error_list = $this->populateErrors();
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>$error_list);
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
    
    private function addFilters($query, $filters){
        if(isset($filters))
        {
            $filter_object = Json::decode($filters, true);
            if(isset($filter_object['search_text'])){
                // Use query builder expressions for performance improvement
                $query->where("name LIKE :group_name", 
                        [":group_name" => "%{$filter_object['search_text']}%"]);
            }
        }
    }
	
    public function read(RecordFilter $recordFilter){
        $query = Facility::find();
        
        $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
        $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);
        $this->addFilters($query, $recordFilter->filter);
        
        $record_count = $query->count();
        
        $data = array("total_records" => $record_count, "records" => $query->all());
        return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
    }
}

