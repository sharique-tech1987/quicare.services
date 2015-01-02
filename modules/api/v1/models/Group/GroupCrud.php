<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\v1\models\Group\Group;

class GroupCrud{
    
    private $group;
    
    public function __construct() {
        $this->group = new Group();
    }
    
    public function create($params){
        $this->group->scenario = 'post';
        $this->group->attributes = $params;
        return $this->group->postGroup();
    }
    
    public function update($id, $params){
        if (($this->group = Group::findOne($id)) !== null) {
            $this->group->scenario = 'put';
            $this->group->attributes = $params;
            return $this->group->putGroup();
        } 
        else {
            return array('success'=>false ,'data'=>array(), 
                'errors'=>array("Could not find record"));
        }
    }
    
    public function read($id=null){
        if (isset($id)) {
            if (($this->group = Group::findOne($id)) !== null) {
                return array('success'=>true ,'data'=>array($this->group->attributes), 
                    'errors'=>array());
            }
            else {
                return array('success'=>false ,'data'=>array(), 
                    'errors'=>array("Could not find record"));
            }
            
        }
        
        
    }
    
}