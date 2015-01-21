<?php
namespace app\modules\api\controllers;

use yii\rest\Controller;
use app\modules\api\models\AuthToken\AuthTokenCrud;
use app\modules\api\models\ServiceResult;
//use app\modules\api\models\AuthToken\AuthToken;
//use app\modules\api\components\CryptoLib;
//use app\modules\api\v1\models\User\User;

use Yii;

class LoginController extends Controller
{
    private $response;
//    private $crud;
    
    public function init() {
        parent::init();
//        $this->crud = new AuthTokenCrud();
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
            
            $userName = base64_decode($params["user_name"]);
            $password = base64_decode($params["password"]);
            $this->response->data = AuthTokenCrud::create($userName, $password);
            
            
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
