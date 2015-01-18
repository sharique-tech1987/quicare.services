<?php


namespace app\modules\api\v1\models\UserFacility;

use app\modules\api\v1\models\Facility\Facility;
use \yii\db\ActiveRecord;

class UserFacility extends ActiveRecord{
    public static function tableName()
    {
        return 'user_health_care_facility';
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
            'default' => ['facility_id', '!user_id']
        ];
    }
    
    public function rules() {
        
        return [ 
            [['user_id', 'facility_id' ], 'required', 
                 'message' => '{attribute} required',  ],
            [['facility_id'], 'exist',  'targetClass' => Facility::className(), 
                'targetAttribute' => 'id', 
                'message' => 'Foreign key violation. Facility id does not exist']
            
        ];
    }
    
    public static function deleteUsersFacilities($user_id){
        self::deleteAll('user_id = :id', ['id' => $user_id]);
    }
    
}