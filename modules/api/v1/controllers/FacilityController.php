<?php
namespace app\modules\api\v1\controllers;

use yii\rest\Controller;
use app\modules\api\v1\models\Facility\FacilityCrud;
use app\modules\api\v1\models\Facility\Facility;
use app\modules\api\v1\models\FacilityGroup\FacilityGroup;
use app\modules\api\models\ServiceResult;
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
        try {
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            $params = $this->trimParams($params);

            $facility = new Facility();
            $facility->scenario = 'post';
            $facility->attributes = $params;

            $facilityGroups;

            if (isset($params["group_id"]) && is_int($params["group_id"])){
                $facilityGroups = new FacilityGroup();
                $facilityGroups->attributes = $params;
            }
            else if(isset($params["group_id"]) && is_array($params["group_id"])){
                $facilityGroups = array();
                $groups_ids = $params["group_id"];
                foreach ($groups_ids as $value) {
                        $tempFgObject = new FacilityGroup();
                        $tempFgObject->group_id = $value;
                        array_push($facilityGroups, $tempFgObject);
                    }
            }

            $this->response->data = $this->facilityCrud->create($facility, $facilityGroups);
            
        } catch (\Exception $ex) {
            $this->response->statusCode = 500;
            $serviceResult = new ServiceResult();
            $serviceResult = array('success'=>false, 'data'=>array(), 
                                    'error_lst'=>array("exception" => $ex->getMessage()) );
            $this->response->data = $serviceResult;
        }
        
        
    }
	
    public function actionUpdate($id){
            
            $params = Yii::$app->request->post();
            date_default_timezone_set("UTC");

            $this->response->statusCode = 200;
            
            $this->response->data = $this->facilityCrud->update($id, $params);
        
        }
	
    public function actionDelete($id){
        $this->response->statusCode = 500;
        $serviceResult = new ServiceResult();
        $serviceResult = array('success'=>false, 'data'=>array(), 
                                'error_lst'=>array("exception" => 
                                    "Delete method not implemented for this resource") );
        $this->response->data = $serviceResult;
        
    }
 
        
    private function trimParams($params){
        if(isset($params["deactivate"])){
            $params["deactivate"] = strtoupper(trim($params["deactivate"]));
        }
        if(isset($params["representative_name"])){
            $params["representative_name"] = trim($params["representative_name"]);
        }
        
        if(isset($params["city"])){
            $params["city"] = trim($params["city"]);
        }
        
        if(isset($params["npi"])){
            $params["npi"] = trim($params["npi"]);
        }
        
        if(isset($params["phone"])){
            $params["phone"] = trim($params["phone"]);
        }
        
        if(isset($params["representative_contact_number"])){
            $params["representative_contact_number"] = 
                trim($params["representative_contact_number"]);
        }
        
        if(isset($params["ein"])){
            $params["ein"] = trim($params["ein"]);
        }
        
        if(isset($params["zip_code"])){
            $params["zip_code"] = trim($params["zip_code"]);
        }
    
        return $params;
    }
}