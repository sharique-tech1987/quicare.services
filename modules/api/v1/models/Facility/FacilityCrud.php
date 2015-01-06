<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\v1\models\Facility\Facility;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;

class FacilityCrud{
    
    private $facility;
    private $serviceResult;
    
    public function __construct() {
        $this->facility = new Facility();
        $this->serviceResult = new ServiceResult();
    }
    
    public function create($params){
        $this->facility->scenario = 'post';
        $this->facility->attributes = $params;
        $this->serviceResult->attributes = $this->facility->postFacility();
        return $this->serviceResult;
    }
    
    public function update($id, $params){
        if (($this->facility = Facility::findOne($id)) !== null) {
            $this->facility->scenario = 'put';
            $this->facility->attributes = $params;
            $this->serviceResult->attributes = $this->facility->putFacility();
            return $this->serviceResult;
        } 
        else {
            $this->serviceResult->attributes = array('success'=>false, 'data'=>array(), 
                                                'error_lst'=>array("record" =>  "Could not find record"));
            return $this->serviceResult;
            
        }
    }
    
    public function read($id=null, $params=null){
        if (isset($id)) {
            if (($this->facility = Facility::findOne($id)) !== null) {
                $this->serviceResult->attributes = array('success'=>true, 
                                                    'data'=>array($this->facility->attributes), 
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
                $this->serviceResult->attributes = $this->facility->read($recordFilter);
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
