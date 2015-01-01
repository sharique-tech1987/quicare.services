<?php

namespace app\modules\api\v1\models;
use \yii\base\Exception;

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
            [['name'], 'required'],
            [['name'], 'unique', 'message'=> 'Please enter a unique hospital group name'],
            [['administrator'], 'integer'],
            [['administrator'], 'isUserExist'],
            [['deactivate'], 'hasValidCharacter']
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Set current date in created_on and updated_on
            if ($insert)
                $this->created_on = date("Y-m-d H:i:s", time());

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
    }
    
    public function hasValidCharacter($attribute,$params){
        /*
         * Check if given character has 'F' or 'T'
         */
        $value = strtolower(trim($this->$attribute));
        if($value != 'f' || $value != 't'){
            $this->addError($attribute, "Invalid entry");
        }
    }
    
    public function postGroup(){
        if ($this->save()) {
            $this->success= true;
            $this->data = $this->id;
                    
            return array('success'=>$this->success ,'data'=>$this->data , 'errors'=>$this->error_lst);

        } 
        
        else{
                foreach($this->errors as $key => $value){
                    array_push($this->error_lst, $value[0]);
                }

                return array('success'=>$this->success ,'data'=>$this->data , 'errors'=>$this->error_lst);

        }
    }
	
    
}

