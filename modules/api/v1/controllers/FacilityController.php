<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\Facility\FacilityCrud;
use Yii;

class FacilityController extends Controller
{
    private $response;
    private $facilityCrud;
    
    public function init() {
        parent::init();
        $this->facilityCrud = new FacilityCrud();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function actionIndex(){
        $params = Yii::$app->request->get();
        
        $this->response->statusCode = 200;
        $this->response->data = $this->facilityCrud->read($id=null, $params=$params);
        
    }
	
	
	public function actionView($id){
		$this->response->statusCode = 200;
            
        $this->response->data = $this->facilityCrud->read($id);
	}
	
	public function actionCreate(){
        $params = Yii::$app->request->post();
        date_default_timezone_set("UTC");

        $this->response->statusCode = 200;
        
        $this->response->data = $this->facilityCrud->create($params);
        
    }
	
    public function actionUpdate($id){
            
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            
            $this->response->data = $this->facilityCrud->update($id, $params);
        
        }
	
    public function actionDelete($id){
            
        }
    
}