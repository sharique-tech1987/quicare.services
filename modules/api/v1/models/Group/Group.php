<?php

namespace app\modules\api\v1\models\Group;

use yii\db\ActiveRecord;

class Group extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group';
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
    public function rules()
    {
        return [
            [['name'], 'required', 'on' => ['post'], 'message' => 'Please enter a unique hospital group name' ],
            [['name'], 'unique', 'message' => 'Please enter a unique hospital group name', 'on' => ['post', 'put'] ],
            [['administrator'], 'integer', 'on' => ['post', 'put'] ],
            [['administrator'], 'isUserExist', 'on' => ['post', 'put']],
            [['deactivate'], 'hasValidCharacter', 'on' => ['put']]
        ];
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = ['name', 'administrator'];
        $scenarios['put'] = ['name', 'administrator', 'deactivate'];
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
    
    public function isUserExist($attribute,$params){
        /*
         * Check if given user is exist in user table
         */
        // Call User::findOne($id) to check
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
    
    public function postGroup(){
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
    
    public function putGroup(){
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
	
    
}

