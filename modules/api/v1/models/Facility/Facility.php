<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\AppQueries;
use app\modules\api\models\RecordFilter;
use yii\helpers\Json;
use yii\db\ActiveRecord;

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
                            'type',  'npi', 'phone', 'email', 'representative_name',  
                            'representative_contact_number', 'representative_email' ], 'required', 
                'on' => ['post'], 'message' => '{attribute} should not be empty',  ],
            [['name',  'npi'],  'unique', 'on' => ['post', 'put'], 
                'message' => '{attribute} should be unique' ],
            [['email', 'representative_email' ], 'email', 'on' => ['post', 'put'] ],
            [['zip_code'], 'compare', 'compareValue' => 0, 'operator' => '>', 
                'on' => ['post', 'put'], "message" => "Please enter a valid 5 "
                . "digit zip code of the healthcare facility"],
            [ ['zip_code'], 'string', 'length' => [5, 5], 'on' => ['post', 'put'] ],
            [['npi', 'phone', 'representative_contact_number'], 
                'compare', 'compareValue' => 0, 'operator' => '>', 
                'on' => ['post', 'put'], "message" => "{attribute} should be 10 digits"  ],
            [['npi', 'phone', 'representative_contact_number'], 
                'string', 'length' => [10, 10], 'on' => ['post', 'put']  ],
            [['city'], 'match', 'pattern' => '/[^A-Za-z]/' 
                , 'not' => true, 'message' => "Please enter alphabets only", 
                'on' => ['post', 'put'] ],
            [['state'], 'hasValidState', 'on' => ['post', 'put'] ],
            [['type'], 'hasValidFacilityType', 'on' => ['post', 'put'] ],
            [['representative_name'], 'match', 'pattern' => "/[^A-Za-z\s-'.,]/", 
                'not' => true, 'message' => "Please enter a representative's "
                . "name at the healthcare facility", 
                'on' => ['post', 'put'] ],
            [['designated_representative'], 'hasValidRepresentative'],
            [['deactivate'], 'in', 'range' => ['F', 'T'], 'strict' => true, 
                'on' => ['put'], "message" => "Please enter valid deactivate value"],
        ];
    }
    
    public function hasValidRepresentative($attribute,$params){
        /*
         * Designated Quicare representative record should exist in user table
         * Check this field only when facility type is clinic, fsed, ed
         */
    }
    
    public function hasValidState($attribute,$params){
        /*
         * State should be valid 2 character code which exist in state table
         */
        
        $value = $this->$attribute;
        $rows = AppQueries::findState($value);
        if(!$rows){
            $this->addError($attribute, "Please enter valid state");
        }
    }
    
    public function hasValidFacilityType($attribute,$params){
        /*
         * Facility type should be valid 2 character code which exist in 
         * health_care_facility_type table
         */
        
        $value = $this->$attribute;
        $rows = AppQueries::findFacilityType($value);
        if(!$rows){
            $this->addError($attribute, "Please enter valid facility type");
        }
        
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = ['name', 'address1', 'address2', 'city', 'zip_code', 'state',  
                            'type', 'npi', 'phone', 'email', 'representative_name',  
                            'representative_contact_number',  'representative_email',  
                            'designated_representative', 'default_group' ];
        
        $scenarios['put'] = ['name', 'address1', 'address2', 'city', 'zip_code', 'state',  
                            'type', 'npi', 'phone', 'email', 'representative_name',  
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
    
    public function getReadQuery(RecordFilter $recordFilter){
        $query = self::find();
        
        $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
        $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);
        
        return $query;
    }
	
    public function read(RecordFilter $recordFilter){
        $query = self::find();
        
        $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
        $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);
        
        $this->addFilters($query, $recordFilter->filter);
        
        $record_count = $query->count();
        
        $data = array("total_records" => $record_count, "records" => $query->all());
        return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
    }
}

