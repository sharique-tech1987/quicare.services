<?php

namespace app\modules\api\v1\models\Specialty;

use yii\db\ActiveRecord;

class Specialty extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'specialty';
    }
 
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['short_name'];
    }
    
    public static function isSpecialtyExist($specialtyCode){
        return self::find()->where("short_name = :name", 
                        [":name" => $specialtyCode])->exists();
    }
    
}

