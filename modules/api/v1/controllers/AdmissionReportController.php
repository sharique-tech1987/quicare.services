<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\models\ReportQueries;
use app\modules\api\models\AppEnums;

use Yii;

class AdmissionReportController extends Controller
{
    private $response;
    
    public function init() {
        parent::init();
        $this->response = Yii::$app->response;
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        $this->response->headers->set('Content-type', 'application/json; charset=utf-8');
    }
    
    public function actionIndex(){
        try {
            $params = Yii::$app->request->get();
        
            $this->response->statusCode = 200;
            $reportsData = [];

            foreach (ReportQueries::getAdmissionStatus() as $value) {
                $tempData = [ "adm_date" => $value["adm_date"], 
                    AppEnums::getStatusText($value["adm_status"]) => $value["adm_count"] ];
                for($i=1; $i<=8; $i++){
                    if($value["adm_status"] != $i){
                        $tempData[AppEnums::getStatusText($i)] = "0";
                    }
                }
                array_push($reportsData, $tempData);
            }
//            $reportsData = array( array( "adm_date"=> "2015-12-07", "Initiated" => 1, "Accepted" => 1, "Denied" => 0, "Bed Assigned" => 2, "Patient Arrived" =>5, "Patient No-Show" => 1, "Closed" => 0, "Discharged" => 8),
//                    array( "adm_date"=> "2015-12-08", "Initiated" => 0, "Accepted" => 3, "Denied" => 2, "Bed Assigned" => 4, "Patient Arrived" =>8, "Patient No-Show" => 2, "Closed" => 1, "Discharged" => 10), 
//                array( "adm_date"=> "2015-12-09", "Initiated" => 2, "Accepted" => 2, "Denied" => 4, "Bed Assigned" => 3, "Patient Arrived" =>9, "Patient No-Show" => 3, "Closed" => 2, "Discharged" => 6));
            
            
            $this->response->data = $reportsData;
            
            
        } 
        catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult(false, $reportsData = array(), 
                $errors = array("exception" => $ex->getMessage()));
            $this->response->data = $serviceResult;
        }
        
        
    }
	
	
    public function actionView($id){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "View method not implemented for this resource" ));
        $this->response->data = $serviceResult;
	}
	
    public function actionCreate(){
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Create method not implemented for this resource" ));
        $this->response->data = $serviceResult;
        
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
