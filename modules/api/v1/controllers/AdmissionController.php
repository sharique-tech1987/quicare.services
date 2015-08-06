<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\RecordFilter;
use app\modules\api\models\AuthToken\AuthTokenCrud;
use app\modules\api\v1\models\Admission\Admission;
use app\modules\api\v1\models\Admission\AdmissionCrud;
use app\modules\api\v1\models\AdmissionDiagnosis\AdmissionDiagnosis;

use Yii;

class AdmissionController extends Controller
{
    private $response;
    private $crud;
    private $authUser;
    
    public function init() {
        parent::init();
        $this->crud = new AdmissionCrud();
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
        try {
            $params = Yii::$app->request->get();
        
            $this->response->statusCode = 200;

            $recordFilter = new RecordFilter();

            $recordFilter->attributes = $params;
            
            $result = $this->crud->readAll($recordFilter, true);
            
            if(isset($params["export_csv"])){
                $result = $result->data["records"];
                $this->downloadCSV($result, 'facilities');
                
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
            $params = Yii::$app->request->get();
            $this->response->statusCode = 200;
            $recordFilter = new RecordFilter();
            $recordFilter->id = $id;
            $recordFilter->attributes = $params;
            
            $admission = $this->crud->read($recordFilter, $findModel = false);
            $serviceResult = new ServiceResult(true, 
                $data = $admission , 
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
        try {
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            $params = $this->trimParams($params);

            $admission = new Admission();
            $admission->scenario = 'post';
            $admission->attributes = $params;
            $admissionDiagnosis = $this->getAdmissionDiagnosis($params);
            $this->response->data = $this->crud->create($admission, $admissionDiagnosis);
            
        } 
        catch (\Exception $ex) {
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
                        
            $facility = FacilityCrud::read($recordFilter);
            $facility->scenario = 'put';
            $params = $this->trimParams($params);
            $facility->attributes = $params;
            
            $facilityGroups = $this->getFacilityGroup($params);

            $this->response->data = $this->crud->update($facility, $facilityGroups);
                
            
            
        } 
        catch (\Exception $ex) {
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
        if(isset($params["patient_gender"])){
            $params["patient_gender"] = strtoupper(trim($params["patient_gender"]));
        }
    
        return $params;
    }
    
    private function getFacilityGroup($params){
        $facilityGroups = null;
//      Refactor this and return array only
        if (isset($params["diagnosis_code"]) && is_int($params["diagnosis_code"])){
            $facilityGroups = new FacilityGroup();
            $facilityGroups->attributes = $params;
        }
        else if(isset($params["diagnosis_code"]) && is_array($params["diagnosis_code"])){
            $facilityGroups = array();
            $groups_ids = $params["diagnosis_code"];
            foreach ($groups_ids as $value) {
                    $tempFgObject = new FacilityGroup();
                    $tempFgObject->diagnosis_code = $value;
                    array_push($facilityGroups, $tempFgObject);
                }
        }
        
        return $facilityGroups;
    }
    
    private function downloadCSV($data, $fileName){
        $validFields = ['name' => 'name', 'type' => 'type', 
            'created' => 'created', 'updated' => 'updated'];
        if(sizeof(array_filter(array_intersect_key($validFields, $data[0]))) != 4){
            throw new \Exception('Data could not contain required fields for csv');
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=$fileName.csv");

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv($output, array('Healthcare Facility Name', 'Facility Type', 'Created', 'Updated'));
        foreach ($data as $r) {
            fputcsv($output, array($r['name'], $r['type'], $r['created'], $r['updated']));
        }
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
    
    private function getAdmissionDiagnosis($params){
        $admissionDiagnosis = null;

        if(isset($params["diagnosis"]) && (is_array($params["diagnosis"])) ){
            $admissionDiagnosis = array();
            $diagnosisArray = $params["diagnosis"];
            foreach ($diagnosisArray as $value) {
                    $tempAdmissionDiagnosisObject = new AdmissionDiagnosis();
                    $tempAdmissionDiagnosisObject->diagnosis_code = $value["code"];
                    $tempAdmissionDiagnosisObject->diagnosis_desc = $value["desc"];
                    array_push($admissionDiagnosis, $tempAdmissionDiagnosisObject);
                }
        }
        
        return $admissionDiagnosis;
    }
    
}
