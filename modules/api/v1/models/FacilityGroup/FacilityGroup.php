<?php


namespace app\modules\api\v1\models\FacilityGroup;

use \yii\db\ActiveRecord;

class FacilityGroup extends ActiveRecord{
    public static function tableName()
    {
        return 'health_care_facility_group';
    }
    
    /*
     * group_id and facility_id will be mandatory
     * facility_id will not be massively assigned
     */
 
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }
    
    public function scenarios() {
        return [
            'default' => ['group_id']
        ];
    }
    
}