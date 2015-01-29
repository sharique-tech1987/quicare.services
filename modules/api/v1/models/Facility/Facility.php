<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\AppQueries;
use yii\db\ActiveRecord;
use app\modules\api\v1\models\Group\Group;
use app\modules\api\v1\models\User\User;
use app\modules\api\v1\models\FacilityType\FacilityType;
use app\modules\api\v1\models\State\State;
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
        if(!State::isStateExist($value)){
            $this->addError($attribute, "Please enter valid state");
        }
    }
    
    public function hasValidFacilityType($attribute,$params){
        /*
         * Facility type should be valid 2 character code which exist in 
         * health_care_facility_type table
         */
        
        $value = $this->$attribute;
        if(!FacilityType::isFacilityTypeExist($value)){
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
    
    public function getGroups()
    {
        return $this->hasMany(Group::className(), ['id' => 'group_id'])
            ->viaTable('health_care_facility_group', ['facility_id' => 'id']);
    }
    
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('user_health_care_facility', ['facility_id' => 'id']);
    }

    public static function addFilters($query, $filters){
        if(isset($filters))
        {
            $filter_object = Json::decode($filters, true);
            $search_type = isset($filter_object['search_type']) ? 
                $filter_object['search_type'] : null;
            $search_by = isset($filter_object['search_by']) ? 
                $filter_object['search_by'] : null;
            
            $search_text = isset($filter_object['search_text']) ?
                $filter_object['search_text'] : null;
            
            if(isset($search_text) && $search_by == "hf_type"){
                $search_text = explode(",", $search_text);
                
            }
            
            if($search_type == "all_hf" && $search_by == "all"){
                // Use query builder expressions for performance improvement
//              This condition and else condition is same.
            }
            else if($search_type == "all_hf" && $search_by == "hf_name" && $search_text){
                $query->where("[[name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "all_hf" && $search_by == "hf_type" && $search_text ){
                $query->where(["type" => $search_text]);
            }
            else if($search_type == "all_hf" && $search_by == "hg_name" && $search_text){
                $query->innerJoinWith('groups', false)
                    ->andWhere("[[group.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }

//          Active Healthcare Facilities
            else if($search_type == "active_hf" && $search_by == "all"){
                $query->andWhere(["category" => "A"]);
            }
            else if($search_type == "active_hf" && $search_by == "hf_name" && $search_text){
                $query->where("[[name]] LIKE :search_text")
                       ->andWhere(["category" => "A"]);
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "active_hf" && $search_by == "hf_type" && $search_text){
                $query->where(["type" => $search_text])
                      ->andWhere(["category" => "A"]);
            }
            else if($search_type == "active_hf" && $search_by == "hg_name" && $search_text){
                $query->innerJoinWith('groups', false)
                    ->where(["health_care_facility.category" => "A"])
                    ->andWhere("[[group.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            
//          Test Healthcare Facilities
            else if($search_type == "test_hf" && $search_by == "all"){
                $query->andWhere(["category" => "T"]);
            }
            else if($search_type == "test_hf" && $search_by == "hf_name" && $search_text){
                $query->where("[[name]] LIKE :search_text")
                       ->andWhere(["category" => "T"]);
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "test_hf" && $search_by == "hf_type" && $search_text){
                $query->where(["type" => $search_text])
                      ->andWhere(["category" => "T"]);
            }
            else if($search_type == "test_hf" && $search_by == "hg_name" && $search_text){
                $query->innerJoinWith('groups', false)
                    ->where(["health_care_facility.category" => "T"])
                    ->andWhere("[[group.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }            
            
        }
    }
    
    public static function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
    }
    
    public static function addSortFilter($query, $orderby, $sort){
        if( !(isset($orderby) && isset($sort)) ) {
            $orderby = 'health_care_facility.updated_on';
            $sort = SORT_DESC;
        }
        else{
            $orderby = 'health_care_facility.' . $orderby;
            $sort = strtoupper($sort) === 'ASC' ? SORT_ASC : SORT_DESC;
        }
        $query->orderBy([$orderby => $sort]);

    }
}

