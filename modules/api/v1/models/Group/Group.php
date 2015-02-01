<?php

namespace app\modules\api\v1\models\Group;

use yii\db\ActiveRecord;
use app\modules\api\v1\models\Facility\Facility;
use app\modules\api\v1\models\User\User;
use yii\helpers\Json;

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
    
    public function getFacilities()
    {
        return $this->hasMany(Facility::className(), ['id' => 'facility_id'])
            ->viaTable('health_care_facility_group', ['group_id' => 'id']);
    }
    
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('user_group', ['group_id' => 'id']);
    }
    
    public static function addFilters($query, $filters){
        if(isset($filters))
        {
            $filter_object = Json::decode($filters, true);
            $search_type = isset($filter_object['search_type']) ? 
                $filter_object['search_type'] : null;
            $search_by = isset($filter_object['search_by']) ? 
                $filter_object['search_by'] : null;
            
            $search_text = isset($filter_object['search_text']) ?
                $filter_object['search_text'] : null;
            
            $isReal = $search_type === "active_hg" ? 'T' : 'F';
            $validSearchTypeValues = array("active_hg", "test_hg");
                        
            if($search_type == "all_hg" && $search_by == "all"){
                // Use query builder expressions for performance improvement
//              This condition and else condition is same.
            }
            else if($search_type == "all_hg" && $search_by == "hg_name" && $search_text){
                $query->where("[[name]] LIKE :name");
                $query->addParams([":name" => "%{$search_text}%"]);
            }
            
//          Active Groups
            else if(in_array($search_type, $validSearchTypeValues) && $search_by == "all"){
                $query->andWhere(["isReal" => $isReal]);
            }
            else if(in_array($search_type, $validSearchTypeValues) && $search_by == "hg_name" && $search_text){
                $query->where("[[name]] LIKE :name")
                       ->andWhere(["isReal" => $isReal]);
                $query->addParams([":name" => "%{$search_text}%"]);
            }

            else{
            }
        }
    }
    
    public static function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
    }
    
    public static function addSortFilter($query, $orderby, $sort){
        $groupTableCols = self::getTableSchema()->columnNames;
        
        if( !(isset($orderby) && isset($sort)) || (!in_array($orderby, $groupTableCols))  ) {
            $orderby = 'group.updated_on';
            $sort = SORT_DESC;
        }
        else{
            $orderby = 'group.' . $orderby;
            $sort = strtoupper($sort) === 'ASC' ? SORT_ASC : SORT_DESC;
        }
        $query->orderBy([$orderby => $sort]);

    }
    
    public function fields() {
        return [
            'id', 
            'name', 
            'created' => 'created_on',
            'updated' => 'updated_on',
            
        ];
    }
    
    public function extraFields() {
        return [
            'administrator',
            'disabled' => 'deactivate',
            'is_real' => 'isReal'
            
        ];
    }
}

