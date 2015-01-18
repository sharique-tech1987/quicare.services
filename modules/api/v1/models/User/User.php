<?php

namespace app\modules\api\v1\models\User;

use app\modules\api\models\AppQueries;
use yii\db\ActiveRecord;
use \app\modules\api\v1\models\Degree\Degree;
use app\modules\api\v1\models\Specialty\Specialty;
use app\modules\api\v1\models\UserRole\UserRole;

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
                'cell_phone', 'category', 'role', ], 'required', 
                'on' => ['post'], 'message' => '{attribute} should not be empty',  ],

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
                                'cell_phone', 'category', 'role', 'degree', 'npi', 'specialty', ];
        
        $scenarios['put'] = [ 'first_name', 'middle_name', 'last_name', 'user_name', 'email',
                                'cell_phone', 'category', 'role', 'degree', 'npi', 'specialty',
                                'notify', 'enable_two_step_verification', 'deactivate', 
                                'time_zone', ];
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
    
    
    
}

