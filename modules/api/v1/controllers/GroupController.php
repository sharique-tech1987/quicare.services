<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\Group\GroupCrud;
use app\modules\api\v1\models\Group\Group;
use Yii;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;

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
        try {
            $params = Yii::$app->request->get();
        
            $this->response->statusCode = 200;

            $recordFilter = new RecordFilter();

            $recordFilter->attributes = $params;
            
            $result = $this->groupCrud->readAll($recordFilter, true);
            
//            $records = $result->data["records"];
            
            if(isset($params["export_csv"])){
                $result = $result->data["records"];
//                $this->response->headers->set('Content-Type: text/csv; charset=utf-8');
//                $this->response->headers->set('Content-Disposition: attachment; filename=groups.csv');
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=groups.csv');

                // create a file pointer connected to the output stream
                $output = fopen('php://output', 'w');

                // output the column headings
                fputcsv($output, array('Group', 'Created', 'Updated', 'Affiliated Hospital'));
                foreach ($result as $f) {
                    fputcsv($output, $f);
                }
            }
            
            else{
                $this->response->data = $result;
            }
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
            
            $group = $this->groupCrud->read($recordFilter, $findModel = false);
            
            $serviceResult = new ServiceResult(true, 
                $data = $group, 
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
        try{
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;

            $group = new Group();
            $group->scenario= 'post';
            $group->attributes = $params;
                
        $this->response->data = $this->groupCrud->create($group);
            
        } catch (\Exception $ex) {
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
            
            $group = $this->groupCrud->read($recordFilter);
            $params = $this->trimParams($params);
            $group->scenario = 'put';
            $group->attributes = $params;
            
            $this->response->data = $this->groupCrud->update($group);
        } catch (\Exception $ex) {
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
        return $params;
    }
    
    
}