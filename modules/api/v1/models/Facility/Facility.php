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
            [['name', 'address1', 'city', 'zip_code', 'state',  
                            'type', 'ein', 'npi', 'phone', 'email', 'representative_name',  
                            'representative_contact_number', 'representative_email' ], 'required', 
                'on' => ['post'], 'message' => '{attribute} should not be empty',  ],
            [['name', 'ein', 'npi'],  'unique', 'on' => ['post', 'put'], 
                'message' => '{attribute} should be unique' ],
            [['email', 'representative_email' ], 'email', 'on' => ['post', 'put'] ],
            [['zip_code'], 'hasFiveDigits', 'on' => ['post', 'put'] ],
            [['ein'], 'hasNineDigits' , 'on' => ['post', 'put'] ],
            [['npi', 'phone', 'representative_contact_number'], 'hasTenDigits', 
                'on' => ['post', 'put']  ],
            [['city'], 'hasAlphabetsOnly', 'on' => ['post', 'put'] ],
            [['state'], 'hasValidState', 'on' => ['post', 'put'] ],
            [['type'], 'hasValidFacilityType', 'on' => ['post', 'put'] ],
            [['representative_name'], 'hasValidSpecialCharacters', 'on' => ['post', 'put'] ],
            [['designated_representative'], 'hasValidRepresentative'],
            [['deactivate'], 'hasValidDeactivateValue', 'on' => ['put']]
        ];
    }
    
    public function hasValidDeactivateValue($attribute,$params)
        {
        /*
         * Check if given character has 'F' or 'T'
         */
        $value = strtoupper(trim($this->$attribute));
        if($value != 'F' && $value != 'T'){
            $this->addError($attribute, "Invalid entry");
        }
    }
    
    public function hasValidRepresentative($attribute,$params){
        /*
         * Designated Quicare representative record should exist in user table
         * Check this field only when facility type is clinic, fsed, ed
         */
    }
    
    public function hasValidSpecialCharacters($attribute,$params){
        /*
         * Representative name should contain alphabets and can contain following characters
         * [- ' . ,]
         */
        $value = trim($this->$attribute);
        if (preg_match("/[^A-Za-z\s-'.,]/", $value)){
            $this->addError($attribute, "Please enter a representative's "
                . "name at the healthcare facility");
        }
    }
    
    public function hasAlphabetsOnly($attribute,$params){
        /*
         * City only contains alphabets
         */
        $value = trim($this->$attribute);
        if (preg_match('/[^A-Za-z\s]/', $value)){
            $this->addError($attribute, "Please enter alphabets only");
        }
    }
    
    public function hasValidState($attribute,$params){
        /*
         * State should be valid 2 character code which exist in state table
         */
        
    }
    
    public function hasValidFacilityType($attribute,$params){
        /*
         * Facility type should be valid 2 character code which exist in 
         * health_care_facility_type table
         */
        
    }
    
    public function hasTenDigits($attribute,$params){
        /*
         * NPI, phone and Representative contact number only contain 10 digits
         */
        $value = trim($this->$attribute);
        if( ((int)$value) < 0 || strlen($value) != 10){
            $this->addError($attribute, ucfirst($attribute) . " should be 10 digit");
        }
    }
    
    public function hasNineDigits($attribute,$params){
        /*
         * EIN only contain 9 digits
         */
        $value = trim($this->$attribute);
        if( ((int)$value) < 0 || strlen($value) != 9){
            $this->addError($attribute, "Please enter a valid 9 digit "
                . "EIN of the healthcare facility");
        }
        
    }

    public function hasFiveDigits($attribute,$params){
        /*
         * Zip code only contain 5 digits
         */
        $value = trim($this->$attribute);
        if( ((int)$value) < 0 || strlen($value) != 5){
            $this->addError($attribute, "Please enter a valid 5 digit "
                . "zip code of the healthcare facility");
        }
        
        
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = ['name', 'address1', 'address2', 'city', 'zip_code', 'state',  
                            'type', 'ein', 'npi', 'phone', 'email', 'representative_name',  
                            'representative_contact_number',  'representative_email',  
                            'designated_representative', 'default_group' ];
        
        $scenarios['put'] = ['name', 'address1', 'address2', 'city', 'zip_code', 'state',  
                            'type', 'ein', 'npi', 'phone', 'email', 'representative_name',  
                            'representative_contact_number',  'representative_email',  
                            'designated_representative', 'default_group', 'deactivate' ];
        return $scenarios;
        
    }
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
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>  $this->errors);
        }
    }
    
    public function putFacility(){
        $this->deactivate = strtoupper(trim($this->deactivate));
        if ($this->save()) {
            $data = array("message" => "Record has been updated");
	 
			return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
	 
		} 
		else{
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>$this->errors);
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

