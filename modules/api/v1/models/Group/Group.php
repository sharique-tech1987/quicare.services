<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\models\BaseResource;
use app\modules\api\models\RecordFilter;
use yii\helpers\Json;

class Group extends BaseResource
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
            [['name'], 'required', 'on' => ['post'], 
                'message' => 'Please enter a unique hospital group name' ],
            [['name'], 'unique', 
                'message' => 'Please enter a unique hospital group name', 'on' => ['post', 'put'] ],
            // Use only one validation rule to validate number and user existance
            [['administrator'], 'integer', 'on' => ['post', 'put'] ],
            [['administrator'], 'isUserExist', 'on' => ['post', 'put']],
            [['deactivate'], 'in', 'range' => ['F', 'T'], 'strict' => true, 
                'on' => ['put'], "message" => "Please enter valid deactivate value"],
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
    
    public function hasValidDeactivateValue($attribute,$params){
        /*
         * Check if given character has 'F' or 'T'
         */
        $value = $this->$attribute;
        if($value != 'F' && $value != 'T'){
            $this->addError($attribute, "Invalid entry");
        }
    }
    
    private function addFilters($query, $filters){
        if(isset($filters))
        {
            $filter_object = Json::decode($filters, true);
            if(isset($filter_object['search_text'])){
                // Use query builder expressions for performance improvement
                $query->where("name LIKE :group_name", 
                        [":group_name" => "%{$filter_object['search_text']}%"]);
            }
        }
    }
	
    public function read(RecordFilter $recordFilter){
        $query = parent::getReadQuery($recordFilter);

        $this->addFilters($query, $recordFilter->filter);
        
        $record_count = $query->count();
        
        $data = array("total_records" => $record_count, "records" => $query->all());
        return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
    }
}

