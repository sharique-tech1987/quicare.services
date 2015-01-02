<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\Group\Group;
use app\modules\api\v1\models\Group\GroupCrud;
use Yii;
use yii\db\Query;

class GroupController extends Controller
{
    private $response;
    private $groupCrud;
    
    public function init() {
        parent::init();
        $this->groupCrud = new GroupCrud();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function actionIndex(){
        
    }
	
	
	public function actionView($id){
		
	}
	
	public function actionCreate(){
        $params = Yii::$app->request->post();
        date_default_timezone_set("UTC");

        $this->response->statusCode = 200;
        
        $this->response->data = $this->groupCrud->create($params);
        
    }
	
        public function actionUpdate($id){
            
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            
            $this->response->data = $this->groupCrud->update($id, $params);
        
        }
	
        public function actionDelete($id){
            
        }
	
	private function findModel($id)
    {
        $response = Yii::$app->response;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-type', 'application/json; charset=utf-8');
        $response->statusCode = 200;
        
        
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        } 
        else {

            $response->data = array('success'=>false ,'data'=>array() , 
                'errors'=>array("Could not find record"));
//          echo json_encode(array('status'=>0,'error_code'=>400,
//              'message'=>'Bad request'),JSON_PRETTY_PRINT);
            exit;
        }
    }
    
}