<?php

namespace app\modules\api\models;

use yii\base\Model;

class ServiceResult extends Model{
    public $success;
    public $data;
    public $error_lst;
    
    public function init() {
        parent::init();
        $this->success = false;
        $this->data = array();
        $this->error_lst = array();
        
    }
    
    public  function scenarios() {
        return [
            'default' => ['data', 'success', 'error_lst'],
        ];
    }
}
