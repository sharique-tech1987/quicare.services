<?php

namespace app\modules\api\v1\models\User;

use app\modules\api\models\AppQueries;
use yii\db\ActiveRecord;

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
                'cell_phone', 'category', 'role', 'degree', 'npi', ], 'required', 
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
            
            [['category'], 'hasValidCategoryAndRole', 'on' => ['post', 'put'] ],
//            [['role'], 'hasValidRole', 'on' => ['post', 'put'] ],
            [['degree'], 'hasValidDegree', 'on' => ['post', 'put'] ],
            [['specialty'], 'hasValidSpecialty', 'on' => ['post', 'put'] ],
            
            [['notify', 'enable_two_step_verification', 'deactivate'], 
                'in', 'range' => ['F', 'T'], 'strict' => true, 
                'on' => ['put'], "message" => "Please enter valid {attribute} value"],
            
        ];
    }
    
    public function hasValidCategoryAndRole($attribute,$params){
        /*
         * Category should be valid 2 character code which exist in category table
         */
    }
    
    public function hasValidRole($attribute,$params){
        /*
         * Role should be valid 2 character code which exist in role table
         */
    }
    
    public function hasValidDegree($attribute,$params){
        /*
         * Degree should be valid 2 character code which exist in degree table
         */
    }
    
    public function hasValidSpecialty($attribute,$params){
        /*
         * Specialty should be valid 2 character code which exist in specialty table
         */
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

