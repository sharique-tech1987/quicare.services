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
        $params = Yii::$app->request->get();
        
        $this->response->statusCode = 200;
        $this->response->data = $this->groupCrud->read($id=null, $params=$params);
        
    }
	
	
	public function actionView($id){
		$this->response->statusCode = 200;
            
        $this->response->data = $this->groupCrud->read($id);
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
    
}