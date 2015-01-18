<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\User\UserCrud;
use app\modules\api\v1\models\User\User;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\v1\models\UserGroup\UserGroup;
use app\modules\api\v1\models\UserFacility\UserFacility;

use Yii;

class UserController extends Controller
{
    private $response;
    private $userCrud;
    
    public function init() {
        parent::init();
        $this->userCrud = new UserCrud();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function actionIndex(){
        try {
            $params = Yii::$app->request->get();
        
            $this->response->statusCode = 200;

            $recordFilter = new RecordFilter();

            $recordFilter->attributes = $params;

            $this->response->data = $this->userCrud->readAll($recordFilter);
        } 
        catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
        
        
    }
	
	
	public function actionView($id){
        try {
            $this->response->statusCode = 200;
            $recordFilter = new RecordFilter();
            $recordFilter->id = $id;
            
            $user = $this->userCrud->read($recordFilter, $findModel = false);
            $serviceResult = new ServiceResult(true, 
                $data = $user , 
                $errors = array()); 
            $this->response->data = $serviceResult;
            
        } 
        catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
	}
	
	public function actionCreate(){
        try {
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            $params = $this->trimParams($params);

            $user = new User();
            $user->scenario = 'post';
            $user->attributes = $params;

            $userGroups = $this->getUserGroup($params);
            $userFacilities = $this->getUserFacilities($params);

            $this->response->data = $this->userCrud->create($user, $userGroups, $userFacilities);
            
        } 
        catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
        
        
    }
    
    public function actionUpdate($id){
        try {
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            
            $recordFilter = new RecordFilter();
            $recordFilter->id = $id;
            
            $user = $this->userCrud->read($recordFilter);
            $user->scenario = 'put';
            $params = $this->trimParams($params);
            $user->attributes = $params;
            
            $userGroups = $this->getUserGroup($params);
            $userFacilities = $this->getUserFacilities($params);

            $this->response->data = $this->userCrud->update($user, $userGroups, $userFacilities);
        
            
        } 
        catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;    
        }
            
        
        }
	
    public function actionDelete($id){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Delete method not implemented for this resource" ));
        $this->response->data = $serviceResult;
    }
 
    private function trimParams($params){
        if(isset($params["deactivate"])){
            $params["deactivate"] = strtoupper(trim($params["deactivate"]));
        }
        
        if(isset($params["npi"])){
            $params["npi"] = trim($params["npi"]);
        }
        
        if(isset($params["degree"])){
            $params["degree"] = strtoupper(trim($params["degree"]));
        }
        
        if(isset($params["specialty"])){
            $params["specialty"] = strtoupper(trim($params["specialty"]));
        }
        
        if(isset($params["category"])){
            $params["category"] = strtoupper(trim($params["category"]));
        }
        
        if(isset($params["role"])){
            $params["role"] = strtoupper(trim($params["role"]));
        }
        
        if(isset($params["notify"])){
            $params["notify"] = strtoupper(trim($params["notify"]));
        }
        
        if(isset($params["enable_two_step_verification"])){
            $params["enable_two_step_verification"] = 
                strtoupper(trim($params["enable_two_step_verification"]));
        }
        
        
    
        return $params;
    }
    
    private function getUserGroup($params){
        $userGroups = null;

        if(isset($params["group_id"]) && 
            ( is_int($params["group_id"]) || is_array($params["group_id"]) ) ){
            $userGroups = array();
            $groups_ids = is_int($params["group_id"]) ? array($params["group_id"]) :
                                                        $params["group_id"];
            foreach ($groups_ids as $value) {
                    $tempUgObject = new UserGroup();
                    $tempUgObject->group_id = $value;
                    array_push($userGroups, $tempUgObject);
                }
        }
        
        return $userGroups;
    }
    
    private function getUserFacilities($params){
        $userfacilities = null;

        if(isset($params["facility_id"]) && 
            ( is_int($params["facility_id"]) || is_array($params["facility_id"]) ) ){
            $userfacilities = array();
            $facility_ids = is_int($params["facility_id"]) ? array($params["facility_id"]) : 
                                                             $params["facility_id"];
            foreach ($facility_ids as $value) {
                    $tempUfObject = new UserFacility();
                    $tempUfObject->facility_id = $value;
                    array_push($userfacilities, $tempUfObject);
                }
        }
        
        return $userfacilities;
    }
    
    
    
    
}
