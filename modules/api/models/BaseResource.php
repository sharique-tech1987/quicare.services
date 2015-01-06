<?php

namespace app\modules\api\models;

use app\modules\api\models\RecordFilter;
use \yii\db\ActiveRecord;
class BaseResource extends ActiveRecord{
    
    public function postGroup(){
        if ($this->save()) {
            $data = array("id" => $this->id);
                    
            return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());

        } 
        else{
            $error_list = $this->populateErrors();
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>$error_list);
        }
    }
    
    public function putGroup(){
        if ($this->save()) {
            $data = array("message" => "Record has been updated");
	 
			return array('success'=>true, 'data'=>$data, 
                'error_lst'=>array());
	 
		} 
		else{
            $error_list = $this->populateErrors();
            return array('success'=>false, 'data'=>array(), 
                'error_lst'=>$error_list);
        }
        
    }
    
    private function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
    }
    
    private function addOrderBy($query, $orderby, $sort){
        if(isset($orderby) && isset($sort)){
            $orderby_exp = $orderby . " " . $sort;
            $query->orderBy($orderby_exp);
        }
    }
    
    public function getReadQuery(RecordFilter $recordFilter){
        $query = self::find();
        
        $this->addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
        $this->addOrderBy($query, $recordFilter->orderby, $recordFilter->sort);
        
        return $query;
    }
    
    private function populateErrors(){
        $error_list = array();
        foreach($this->errors as $key => $value){
            array_push($error_list, $value[0]);
        }
        return $error_list;
    }
}
