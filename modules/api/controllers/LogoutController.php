<?php
namespace app\modules\api\controllers;

use yii\rest\Controller;
use app\modules\api\models\AuthToken\AuthTokenCrud;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\ActivityLogQueries;
use app\modules\api\models\AppLogValues;

use Yii;

class LogoutController extends Controller
{
    private $response;
    
    public function init() {
        parent::init();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function actionIndex(){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Index method not implemented for this resource" ));
        $this->response->data = $serviceResult;
    }
	
	
    public function actionView($id){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "View method not implemented for this resource" ));
        $this->response->data = $serviceResult;
	}
	
    public function actionCreate(){
        try {
            $params = Yii::$app->request->post();
        
            $this->response->statusCode = 200;
            
            $token = isset($params["token"]) ? $params["token"] : null;
            $expired = 't';
            $resp = AuthTokenCrud::update($token, $expired);
            if($resp->success){
                ActivityLogQueries::insertActivity($resp->data["id"], AppLogValues::loggedout, 
                        Yii::$app->request->getUserIP(), Yii::$app->request->absoluteUrl, $params, 
                        "Success");
            }
            else{
                ActivityLogQueries::insertActivity($resp->data["id"], AppLogValues::loggedout, 
                        Yii::$app->request->getUserIP(), Yii::$app->request->absoluteUrl, $params, 
                        "Failed");
            }
            
            $this->response->data = $resp;
            
            
        } 
        catch (\Exception $ex) {
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
 
}
