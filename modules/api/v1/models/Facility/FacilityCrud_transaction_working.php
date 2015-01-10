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
        $params = $this->trimParams($params);
        $this->facility->attributes = $params;
        $this->serviceResult->attributes = $this->facility->post();
        return $this->serviceResult;
    }
    
    private function trimParams($params){
        if(isset($params["deactivate"])){
            $params["deactivate"] = strtoupper(trim($params["deactivate"]));
        }
        if(isset($params["representative_name"])){
            $params["representative_name"] = trim($params["representative_name"]);
        }
        
        if(isset($params["city"])){
            $params["city"] = trim($params["city"]);
        }
        
        if(isset($params["npi"])){
            $params["npi"] = trim($params["npi"]);
        }
        
        if(isset($params["phone"])){
            $params["phone"] = trim($params["phone"]);
        }
        
        if(isset($params["representative_contact_number"])){
            $params["representative_contact_number"] = 
                trim($params["representative_contact_number"]);
        }
        
        if(isset($params["ein"])){
            $params["ein"] = trim($params["ein"]);
        }
        
        if(isset($params["zip_code"])){
            $params["zip_code"] = trim($params["zip_code"]);
        }
    
        return $params;
    }
    
    public function update($id, $params){
        if (($this->facility = Facility::findOne($id)) !== null) {
            $this->facility->scenario = 'put';
            $params = $this->trimParams($params);
            $this->facility->attributes = $params;
            $this->serviceResult->attributes = $this->facility->put();
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
