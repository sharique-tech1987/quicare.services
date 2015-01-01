<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\Group;
use Yii;
use yii\db\Query;

class GroupController extends Controller
{
    
    public function actionIndex(){
        
    }
	
	
	public function actionView($id){
		
	}
	
	public function actionCreate(){
        $params = Yii::$app->request->post();
        date_default_timezone_set("UTC");

        $model = new Group();
        $model->attributes = $params;

        $response = Yii::$app->response;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Content-type', 'application/json; charset=utf-8');


        $response->statusCode = 200;
        $response->data = $model->postGroup();
            
	}
	
        public function actionUpdate($id){
            
        }
	
        public function actionDelete($id){
            
        }
	
	protected function findModel($id)
    {
        if (($model = Country::findOne($id)) !== null) {
                return $model;
        } else {

          echo json_encode(array('status'=>0,'error_code'=>400,
              'message'=>'Bad request'),JSON_PRETTY_PRINT);
          exit;
        }
    }
    
}