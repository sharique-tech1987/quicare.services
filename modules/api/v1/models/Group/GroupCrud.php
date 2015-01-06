<?php

namespace app\modules\api\v1\models\Group;

use app\modules\api\v1\models\Group\Group;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;

class GroupCrud{
    
    private $group;
    private $serviceResult;
    
    public function __construct() {
        $this->group = new Group();
        $this->serviceResult = new ServiceResult();
    }
    
    public function create($params){
        $this->group->scenario = 'post';
        $this->group->attributes = $params;
        $this->serviceResult->attributes = $this->group->post();
        return $this->serviceResult;
    }
    
    public function update($id, $params){
        if (($this->group = Group::findOne($id)) !== null) {
            $this->group->scenario = 'put';
            $this->group->attributes = $params;
            $this->serviceResult->attributes = $this->group->put();
            return $this->serviceResult;
        } 
        else {
            $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>array("record" => "Could not find record"));
            return $this->serviceResult;
            
        }
    }
    
    public function read($id=null, $params=null){
        if (isset($id)) {
            if (($this->group = Group::findOne($id)) !== null) {
                $this->serviceResult->attributes = array('success'=>true, 
                                                    'data'=>array($this->group->attributes), 
                                                    'error_lst'=>array());
                return $this->serviceResult;
            }
            else {
                $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>array("record" => "Could not find record"));
                return $this->serviceResult;
                
            }
            
        }
        else{
            $recordFilter = new RecordFilter();
            $recordFilter->attributes = $params;
            
            if($recordFilter->validate()){
                $this->serviceResult->attributes = $this->group->read($recordFilter);
                return $this->serviceResult;
            }
            else{
                $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>$recordFilter->getErrors());
                return $this->serviceResult;

            }
            
        }
        
        
    }
    
}