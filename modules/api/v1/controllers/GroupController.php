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
            
            $error_lst = array();
            $data = array();
            
            if ($model->save()) {
                    Yii::$app->response->statusCode = 200;
                    $this->setContentType();
                    $data['id'] = $model->id;
                    echo json_encode(array('success'=>true ,'data'=>$data),
                        JSON_PRETTY_PRINT);

            } 
            else{
                    $this->setContentType();
                    Yii::$app->response->statusCode = 400;
                    foreach($model->errors as $key => $value){
                        array_push($error_lst, $value[0]);
                    }

                    
                    echo json_encode(array('success'=>false, 'data'=>$data,
                        'errors'=>$error_lst),JSON_PRETTY_PRINT);
            }
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

          $this->setHeader(400);
          echo json_encode(array('status'=>0,'error_code'=>400,
              'message'=>'Bad request'),JSON_PRETTY_PRINT);
          exit;
        }
    }
    
    private function setHeader($status)
    {

	  $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
	  $content_type="application/json; charset=utf-8";
	
	  header($status_header);
	  header('Content-type: ' . $content_type);
	  //header('X-Powered-By: ' . "Nintriva <nintriva.com>");
  	}
	
	private function setContentType()
    {
	  $content_type="application/json; charset=utf-8";
	  header('Content-type: ' . $content_type);
	  
  	}
    private function _getStatusCodeMessage($status)
    {
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
    }
	
}