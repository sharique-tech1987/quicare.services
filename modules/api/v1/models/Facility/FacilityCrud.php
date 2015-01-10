<?php

namespace app\modules\api\v1\models\Facility;

use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use Yii;

class FacilityCrud{
    
    private $serviceResult;
    
    public function __construct() {
        $this->serviceResult = new ServiceResult();
    }
    
    public function create($facility, $facilityGroups){
        $transaction = Yii::$app->db->beginTransaction();
        $isSaved = $facility->save();
        
//      Errors collection  
        $errors = array();
        
        if ($isSaved) {
            if (isset($facilityGroups)){
                if (is_array($facilityGroups)){
                    foreach ($facilityGroups as $fg) {
                        $fg->facility_id = $facility->id;
                        $isSaved = $fg->save();
                        if(!$isSaved){
//                        Collect Errors
                            $errors = $fg->getErrors();
                            break;
                        }
                    }
                }
                else{
                    $facilityGroups->facility_id = $facility->id;
                    $isSaved = $facilityGroups->save();
                    if(!$isSaved){
//                        Collect Errors
                        $errors = $facilityGroups->getErrors();
                    }
                    
                }
            }
            else{
                // Facility Groups is not set
                $isSaved = false;
                $errors["facility_groups"] = "Facility groups should not be null";
                
            }
            
        }
        else {
//            Collect errors
                $errors = $facility->getErrors();
        }
        
        
        
        
        if ($isSaved) {
            $transaction->commit();
            $data = array("id" => $facility->id);
                    
            $this->serviceResult = array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
            

        } 
        else{
            $transaction->rollBack();
            $this->serviceResult = array('success'=>false, 'data'=>array(), 
                'error_lst'=>$errors);
        }
        
        return $this->serviceResult;
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
