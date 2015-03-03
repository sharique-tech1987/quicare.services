<?php

namespace app\modules\api\v1\models\Admission;

use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\modules\api\v1\models\Facility\Facility;
use app\modules\api\v1\models\User\User;
use app\modules\api\v1\models\Group\Group;

class Admission extends ActiveRecord
{
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admission';
    }
 
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['transaction_number'];
    }
    
    /**
     * Define rules for validation
     */
    public function rules()
    {
        /*
         * Bed type is mandatory.
         * Patient arrival hours and minutes are mandatory.
         */

        return [
            [['transaction_number', 'patient_first_name', 'patient_last_name', 
                'patient_gender', 'patient_dob', 'sent_to_facility', 'group',
                'sent_by_facility', 'sent_by_user'], 
                'required', 'on' => ['post']],
            
            [['sent_by_facility'], 'exist',  'targetClass' => Facility::className(), 
                'targetAttribute' => 'id', 'filter'=>["type" => ["CC", "FT", "ET"], 
                                                      "deactivate" => "F"],
                'message' => 'Facility not exist or facility is not able to send admission', 
                'on' => ['post', 'put']],
            
            [['sent_by_user'], 'exist',  'targetClass' => User::className(), 
                'targetAttribute' => 'id', 'filter'=>function($query){
                    $query->andWhere("(category IN ('CC', 'ET', 'FT') "
                            . "AND role IN ('RE', 'PT', 'PN', 'SF', 'SN')) OR category = 'HR' AND role = 'HR' "
                            . "AND deactivate = 'F' ");
                },
                'message' => 'User not exist or user is not able to send admission', 
                'on' => ['post', 'put']],
            
            [['patient_first_name', 'patient_last_name'], 'match', 
                'pattern' => "/^[A-Za-z\s-'.,]+$/", 
                'message' => "{attribute} should contain alphabets and (-'.,) set "
                . "of characters", 
                'on' => ['post'] ],
            
            [['zip_code'], 'compare', 'compareValue' => 0, 'operator' => '>', 
                'on' => ['post'], "message" => "Please enter a valid 5 "
                . "digit zip code of the healthcare facility"],
            [ ['zip_code'], 'string', 'length' => [5, 5], 'on' => ['post'] ],
            
            [['patient_ssn'], 'compare', 'compareValue' => 0, 'operator' => '>', 
                'on' => ['post'], "message" => "Please enter a valid 9 "
                . "digit ssn"],
            [ ['patient_ssn'], 'string', 'length' => [9, 9], 'on' => ['post'] ],
            
            [['patient_dob'], 'isValidDob', 'on' => ['post']],
            
            [['patient_gender'], 'in', 'range' => ['M', 'F'], 'strict' => true, 
                'on' => ['post'], "message" => "Please enter valid {attribute} value"],
            
            
        ];
    }
    
    public function isValidDob($attribute,$params){
        $value = $this->$attribute;
        if (preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $value, $matches)) {
            if (!checkdate($matches[1], $matches[2], $matches[3])) {
                $this->addError($attribute, "Please enter valid date");
            }
            else{
                $dob = date_create($value)->diff(date_create('today'));
                $this->patient_age_year = $dob->y;
                $this->patient_age_month = $dob->m;
            }
        }
        else{
            $this->addError($attribute, "DOB should be in mm/dd/yyyy format");
        }
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['post'] = ['transaction_number', 'patient_first_name', 'patient_last_name', 
                            'patient_ssn','patient_dob', 'patient_gender',
                            'sent_to_facility', 'group', 'address1', 'address2', 'city', 'state',
                            'zip_code', 'patient_email', 'patient_contact_number', 
                            'sent_by_facility', 'sent_by_user', 'on_behalf_of'];
        return $scenarios;
        
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
    
    
    public static function getLastTransactionId($transactionId){
        return self::find()
            ->select(["transaction_number"])
            ->where(["LIKE", "transaction_number", $transactionId])
            ->orderBy(["created_on" => SORT_DESC])
            ->one();

    }
    
    public function getSentToFacility(){
        return $this->hasOne(Facility::className(), ['id' => 'sent_to_facility']);
    }
    
    public function getSentByFacility(){
        return $this->hasOne(Facility::className(), ['id' => 'sent_by_facility']);
    }

    public function getSentByUser() {
        return $this->hasOne(User::className(), ['id' => 'sent_by_user']);
    }

    public function getGroup() {
        return $this->hasOne(Group::className(), ['id' => 'group']);
    }

}


