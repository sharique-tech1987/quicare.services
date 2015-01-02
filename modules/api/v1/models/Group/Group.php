<?php

namespace app\modules\api\v1\models\Group;

use yii\db\ActiveRecord;

class Group extends ActiveRecord
{
    
    public $error_lst = array();
    public $data = array();
    public $success = false;
	
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
            $this->success = true;
            $this->data["id"] = $this->id;
                    
            return array('success'=>$this->success, 'data'=>$this->data, 
                'errors'=>$this->error_lst);

        } 
        else{
            $this->populateErrors();
            return array('success'=>$this->success, 'data'=>$this->data, 
                'errors'=>$this->error_lst);
        }
    }
    
    private function populateErrors(){
        foreach($this->errors as $key => $value){
            array_push($this->error_lst, $value[0]);
        }
    }
    
    public function putGroup(){
        if ($this->save()) {
            $this->success = true;
            $this->data["message"] = "Record has been updated";
	 
			return array('success'=>$this->success, 'data'=>$this->data, 
                'errors'=>$this->error_lst);
	 
		} 
		else{
			$this->populateErrors();
            return array('success'=>$this->success, 'data'=>$this->data, 
                'errors'=>$this->error_lst);
		}
        
    }
	
    
}

