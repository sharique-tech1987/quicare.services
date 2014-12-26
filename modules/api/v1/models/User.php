<?php

namespace app\modules\api\v1\models;

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
     * Define rules for validation
     */
    public function rules()
    {
        return [
            [['name', 'age'], 'required']
        ];
    }
	
}