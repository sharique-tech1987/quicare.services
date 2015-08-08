<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\models\AuthToken\AuthTokenCrud;
use app\modules\api\models\AppQueries;
use app\modules\api\models\AppEnums;
use app\modules\api\models\Status;

use Yii;

class AdmissionStatusController extends Controller
{
    private $response;
    private $crud;
    private $authUser;
    
    public function init() {
        parent::init();
//        $this->crud = new AdmissionStatusCrud();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function beforeAction($action){
        
        if (parent::beforeAction($action)) {
            $authHeader = Yii::$app->request->headers->get('Authorization');
            $checkAuthData = $this->isValidAuthData($authHeader);
            
            if($checkAuthData["success"]){
                return $checkAuthData["success"];
            }
            else{
                $this->response->statusCode = 500;
                $serviceResult = new ServiceResult($checkAuthData["success"], $data = array(), 
                    $errors = array("exception" => $checkAuthData["message"]));
                $this->response->data = $serviceResult;
                return $checkAuthData["success"];
            }
            
        } else {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => "Unknown exception"));
            $this->response->data = $serviceResult;
            return false;
        }
    }
    
    public function actionIndex(){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Index method not implemented for this resource" ));
        $this->response->data = $serviceResult;
        
    }
	
    public function actionView($id){
        try {
            $params = Yii::$app->request->get();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            $admissionId = isset($id) ? $id : null;
            
            $errors = array();
            if(!$admissionId || !AppQueries::isValidAdmission($admissionId)){
                $errors['admission_id'] = 'Valid Admission Id should be given';
            }
            
            $serviceResult = null;
            if(sizeof($errors) == 0){
                $data = AppQueries::getAdmissionStatuses($admissionId);
                $serviceResult = new ServiceResult(true, $data, $errors = array());
            }
            else{
                $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
            }
            
            return $serviceResult;

            
        } 
        catch (\Exception $ex) {
            $transaction->rollBack();
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
    }
	
    public function actionCreate(){
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            $admissionId = isset($params["admission_id"]) ? $params["admission_id"] : null;
            $status = isset($params["status"]) ? $params["status"] : null;
            $errors = array();
            if(!$admissionId || !AppQueries::isValidAdmission($admissionId)){
                $errors['admission_id'] = 'Valid Admission Id should be given';
            }
            if(!$status || !in_array($status, AppEnums::getStatusArray())){
                $errors['status'] = 'Valid Status should be given';
            }
            
            if(sizeof($errors) == 0){
                $lastStatus = AppQueries::getLastAdmissionStatus($admissionId);
                if($status == Status::initiated && $lastStatus == false){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else if( ($status == Status::accepted || $status == Status::denied) && 
                        $lastStatus && $lastStatus['status'] == Status::initiated){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else if( $status == Status::bedAllocated && $lastStatus && $lastStatus['status'] == Status::accepted ){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else if( ($status == Status::patientArrived || $status == Status::patientNoShow) 
                        && $lastStatus && $lastStatus['status'] == Status::bedAllocated){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else if( $status == Status::patientDischarged && $lastStatus && $lastStatus['status'] == Status::patientArrived ){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else if( $status == Status::closed && $lastStatus && 
                        ($lastStatus['status'] >= Status::initiated && $lastStatus['status'] <= Status::patientNoShow) ){
                    AppQueries::insertAdmissionStatus($db, $admissionId, $status);
                }
                else{
                    $errors['status'] = 'Valid Status should be given';
                }
                
                $serviceResult = null;
                if(sizeof($errors) == 0){
                    $transaction->commit();
                    $serviceResult = new ServiceResult(true, $data = array(), $errors = array());
                    
                }
                else{
                    $transaction->rollBack();
                    $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
                }
                return $serviceResult;
            }

            
        } 
        catch (\Exception $ex) {
            $transaction->rollBack();
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
        
        
    }
    
    public function actionUpdate($id){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Update method not implemented for this resource" ));
        $this->response->data = $serviceResult;
    }
	
    public function actionDelete($id){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Delete method not implemented for this resource" ));
        $this->response->data = $serviceResult;
    }
 
    private function trimParams($params){
        if(isset($params["patient_gender"])){
            $params["patient_gender"] = strtoupper(trim($params["patient_gender"]));
        }
    
        return $params;
    }
    
    private function isValidAuthData($authHeader){
        if(!isset($authHeader)){
                return array("success" => false, "message" => "Authorization header not found");
            }
        else {
            $token = sizeof(explode('Basic', $authHeader)) >= 2 ? 
                trim(explode('Basic', $authHeader)[1]) : null;
            $user = AuthTokenCrud::read($token);
            if($user === null){
                return array("success" => false, "message" => "Not a valid token");
            }
            else{
                $this->authUser = $user;
                return array("success" => true, "message" => "");
            }
        }
    }
    
}
