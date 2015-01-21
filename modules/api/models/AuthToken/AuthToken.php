<?php

namespace app\modules\api\models\AuthToken;

use yii\db\ActiveRecord;

class AuthToken extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_auth_token';
    }
 
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['token'];
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = [ '!user_id', 'token',];
        
        
        return $scenarios;
        
    }
    
    public function rules() {
        return [
            [ ['user_id', 'token'], 'required', 
                'on' => ['post'], 'message' => '{attribute} should not be empty']
        ];
    }
    
    public function beforeSave($insert){
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

