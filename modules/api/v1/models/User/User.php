<?php

namespace app\modules\api\v1\models\User;

use app\modules\api\models\AppQueries;
use yii\db\ActiveRecord;
use \app\modules\api\v1\models\Degree\Degree;
use app\modules\api\v1\models\Specialty\Specialty;
use app\modules\api\v1\models\UserRole\UserRole;
use app\modules\api\v1\models\Group\Group;
use app\modules\api\v1\models\Facility\Facility;
use app\modules\api\components\CryptoLib;
use yii\helpers\Json;

class User extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
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
            [[ 'first_name', 'last_name', 'user_name', 'email',
                'cell_phone', 'category', 'role', 'password', 'isReal'], 'required', 
                'on' => ['post', 'put'], 'message' => '{attribute} should not be empty',  ],

            [['first_name', 'middle_name', 'last_name',], 'match', 
                'pattern' => "/[^A-Za-z\s-'.,]/", 
                'not' => true, 'message' => "{attribute} should contain alphabets and (-'.,) set "
                . "of characters", 
                'on' => ['post', 'put'] ],
            
            [ ['user_name'], 'unique', 'on' => ['post', 'put']  ],
            [ ['user_name'], 'string', 'length' => [6, 23], 'on' => ['post', 'put'] ],
            [['user_name'], 'match', 
                'pattern' => "/^[^0-9.][a-z.]+[a-z0-9]+$/", 
                'message' => "{attribute} should contain alphabets and periods", 
                'on' => ['post', 'put'] ],
            
            [ ['password'], 'string', 'length' => [8, 23], 'on' => ['post', 'put'] ],
            [ ['password'], 'filter', 'filter' => function ($value) {
                // Generate hash and salt and store it in db
                $this->salt = CryptoLib::generateSalt();
                $value = CryptoLib::hash($value);
                
                return $value;
            }, 'on' => ['post', 'put']],
            
            [['email'], 'email', 'on' => ['post', 'put'] ],
            
            [[ 'npi', 'cell_phone'], 
                'compare', 'compareValue' => 0, 'operator' => '>', 
                'on' => ['post', 'put'], "message" => "{attribute} should be 10 digits"  ],
            [[ 'npi', 'cell_phone'],
                'string', 'length' => [10, 10], 'on' => ['post', 'put']  ],
            
            [['npi'], 'unique', 'message' => '{attribute} should be unique',
                'on' => ['post', 'put'] ],
            
            [['category'], 'hasValidCategoryAndRole', 'on' => ['post', 'put'] ],
//            [['role'], 'hasValidRole', 'on' => ['post', 'put'] ],
            
            [['notify', 'enable_two_step_verification', 'deactivate'], 
                'in', 'range' => ['F', 'T'], 'strict' => true, 
                'on' => ['put'], "message" => "Please enter valid {attribute} value"],
            
            [['npi', 'degree'], 'required', 'when' => function($model) {
                return in_array($this->role, ["PN", "RE", "PT", "BR", "SN"]) ;
            }, 
                'on' => ['post', 'put'], 'message' => "{attribute} required"],
                
            [['degree'], 'hasValidDegree', 'on' => ['post', 'put'] ],
            
            [['npi'], 'isNpiNeeded', 'on' => ['post', 'put'] ],
            
            [['specialty'], 'required', 'when' => function($model) {
                return ( ($this->category == "HL" && $this->role == "PN") || 
                         ($this->category == "CC" && $this->role == "SN") );

            }, 
                'message' => "{attribute} required",
                'on' => ['post', 'put'] ],
            
            [['specialty'], 'hasValidSpecialty', 'on' => ['post', 'put'] ],
            
            [['isReal'], 'in', 'range' => ['F', 'T'], 'strict' => true, 
                'on' => ['post', 'put'], "message" => "Please enter valid {attribute} value"],
            
        ];
    }
    
    public function isNpiNeeded($attribute,$params){
        if (!in_array($this->role, ["PN", "RE", "PT", "BR", "SN"])){
            $this->addError($attribute, "This user role doesn't need npi.");
        }
    }
    
    public function hasValidCategoryAndRole($attribute,$params){
        /*
         * Category should be valid 2 character code which exist in category table
         */
        $category = $this->category;
        $role = $this->role;
        if(!UserRole::isUserRoleExist($category, $role)){
            $this->addError("user_type", "Category or role is not exist");
        }
    }
    
    public function hasValidDegree($attribute,$params){
        /*
         * Degree should be valid 2 character code which exist in degree table
         */
        if (in_array($this->role, ["PN", "RE", "PT", "BR", "SN"])){
            $value = $this->$attribute;
            if(!Degree::isDegreeExist($value)){
                $this->addError($attribute, "Please enter valid degree");
            }
        }
        else{
            $this->addError($attribute, "This user role doesn't need degree.");
        }
    }
    
    public function hasValidSpecialty($attribute,$params){
        /*
         * Specialty should be valid 2 character code which exist in specialty table
         */
        
        if( ($this->category == "HL" && $this->role == "PN") || 
            ($this->category == "CC" && $this->role == "SN") ){
            $value = $this->$attribute;
            if(!Specialty::isSpecialtyExist($value)){
                $this->addError($attribute, "Please enter valid specialty");
            }
        }
        else{
            $this->addError($attribute, "This user role doesn't need specialty.");
        }
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = [ 'first_name', 'middle_name', 'last_name', 'user_name', 'email',
                                'cell_phone', 'category', 'role', 'degree', 'npi', 'specialty', 
                                'password', 'isReal'];
        
        $scenarios['put'] = [ 'first_name', 'middle_name', 'last_name', 'user_name', 'email',
                                'cell_phone', 'category', 'role', 'degree', 'npi', 'specialty',
                                'notify', 'enable_two_step_verification', 'deactivate', 
                                'time_zone', 'password', 'isReal'];
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
            ->viaTable('user_group', ['user_id' => 'id']);
    }
    
    public function getFacilities()
    {
        
        return $this->hasMany(Facility::className(), ['id' => 'facility_id'])
            ->viaTable('user_health_care_facility', ['user_id' => 'id']);
    }
    
    public static function getUser($userName){
        return self::find()->where(["user_name" => $userName])->one();
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
            
            $userCategroy = isset($filter_object['search_category']) ?
                $filter_object['search_category'] : null;
            $userRole = isset($filter_object['search_role']) ?
                $filter_object['search_role'] : null;
            
            $isReal = $search_type === "active_users" ? 'T' : 'F';
            
            if(isset($search_text) && $search_by == "hf_type"){
                $search_text = explode(",", $search_text);
                
            }
            
            if($search_type == "all_users" && $search_by == "all"){
                // Use query builder expressions for performance improvement
//              This condition and else condition is same.
            }
            else if($search_type == "all_users" && $search_by == "u_name" && $search_text){
                $query->where("[[user_name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "all_users" && $search_by == "u_fname" && $search_text ){
                $query->where("[[first_name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "all_users" && $search_by == "u_lname" && $search_text){
                $query->where("[[last_name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "all_users" && $search_by == "u_role" && 
                $userCategroy){
                if($userRole){
                    $query->andWhere(["category" => $userCategroy, "role" => $userRole]);
                }
                else{
                    $query->andWhere(["category" => $userCategroy]);
                }
                
            }
            else if($search_type == "all_users" && $search_by == "u_group" && $search_text ){
                $query->innerJoinWith('groups', false)
                    ->andWhere("[[group.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if($search_type == "all_users" && $search_by == "u_facility" && $search_text){
                $query->innerJoinWith('facilities', false)
                    ->andWhere("[[health_care_facility.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }

//          Active and Test Users
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "all"){
                $query->andWhere(["isReal" => $isReal]);
            }
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "u_name" && $search_text){
                $query->where("[[user_name]] LIKE :search_text")
                    ->andWhere(["isReal" => $isReal]);
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if(in_array($search_type, array("active_users", "test_users")) 
                && $search_by == "u_fname" && $search_text ){
                $query->where("[[first_name]] LIKE :search_text")
                    ->andWhere(["isReal" => $isReal]);
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "u_lname" && $search_text){
                $query->where("[[last_name]] LIKE :search_text")
                    ->andWhere(["isReal" => $isReal]);
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "u_role" && $userCategroy){
                if($userRole){
                    $query->andWhere(["isReal" => $isReal, "category" => $userCategroy, 
                        "role" => $userRole]);
                }
                else{
                    $query->andWhere(["isReal" => $isReal, "category" => $userCategroy]);
                }
                
            }
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "u_group" && $search_text ){
                $query->innerJoinWith('groups', false)
                    ->where(["user.isReal" => $isReal])
                    ->andWhere("[[group.name]] LIKE :search_text");
                $query->addParams([":search_text" => "%{$search_text}%"]);
            }
            else if(in_array($search_type, array("active_users", "test_users")) && 
                $search_by == "u_facility" && $search_text){
                $query->innerJoinWith('facilities', false)
                    ->where(["user.isReal" => $isReal])
                    ->andWhere("[[health_care_facility.name]] LIKE :search_text");
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
        $userTableCols = self::getTableSchema()->columnNames;
        
        if( !(isset($orderby) && isset($sort)) || (!in_array($orderby, $userTableCols))  ) {
            $orderby = 'user.updated_on';
            $sort = SORT_DESC;
        }
        else{
            $orderby = 'user.' . $orderby;
            $sort = strtoupper($sort) === 'ASC' ? SORT_ASC : SORT_DESC;
        }
        $query->orderBy([$orderby => $sort]);

    }
    
    public function fields() {
        return [
            'id',
            'first_name',
            'last_name',
            'user_name',
            'category',
            'role',
            'created' => 'created_on',
            'updated' => 'updated_on',
        ];
    }
    
    public function extraFields() {
        return [
            'middle_name',
            'email',
            'cell_phone',
            'degree',
            'npi',
            'specialty',
            'notify',
            'enable_two_step_verification',
            'disable' => 'deactivate',
            'time_zone',
            'is_real' => 'isReal'   
            
        ];
    }
}

