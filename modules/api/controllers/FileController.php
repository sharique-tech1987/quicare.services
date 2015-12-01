<?php
namespace app\modules\api\controllers;

use yii\rest\Controller;
use app\modules\api\models\ServiceResult;
use app\modules\api\models\AuthToken\AuthTokenCrud;
use app\modules\api\components\CryptoLib;
use app\modules\api\models\AppQueries;


use Yii;

class FileController extends Controller
{
    
    private $response;
    private $authUser;

    public function init() {
        parent::init();
        
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
        $this->response->statusCode = 405;
        $serviceResult = new ServiceResult(false, $data = array(), 
            $errors = array("message" => "Index method not implemented for this resource" ));
        $this->response->data = $serviceResult;
    }
	
    public function actionView($id){}
	
    public function actionCreate(){
        
        $params = Yii::$app->request->post();
        $this->response->statusCode = 200;
        
        
        $validMimeTypes = array('application/pdf' => '.pdf', 'text/plain' => '.txt', 
            'application/msword' => '.doc', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
            'image/png' => '.png', 'image/jpeg' => '.jpg' );
        
        $tempPath = '';
        
        $serviceResult = null;
        $errors = array();
        
        try{    
            $admissionId = isset($params['admission_id']) ? $params['admission_id'] : null;

            if($admissionId != null && !AppQueries::isValidAdmission($admissionId)){
                $errors['admission_id'] = 'Valid Admission Id should be given';
                $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
            }
            else if ( sizeof($errors) == 0 && sizeof( $_FILES ) > 1 ) {
                $errors['file'] = 'Cannot upload multiple files';
                $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
            }

            else if ( sizeof($errors) == 0 && !empty( $_FILES ) ) {
                $tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
                $fileSize = $_FILES[ 'file' ][ 'size' ];
                $lastLine = exec('file -bi '. escapeshellarg($tempPath));
                $fileType = explode(";",$lastLine);

                if(sizeof($fileType) && array_key_exists($fileType[0] , $validMimeTypes)){
                    if($fileSize < 20971520){
                        $fileName = $_FILES[ 'file' ][ 'name' ];
                        $baseUploadPath = '/var/www/uploaded_files/';
                        if($admissionId != null){
                            if (file_exists($baseUploadPath . $admissionId)) {
                                $admissionFolderPath = $baseUploadPath . $admissionId . '/';
                                move_uploaded_file( $tempPath, $admissionFolderPath . $fileName );
                                $uniqueFileName = CryptoLib::randomString(10) .  
                                        strtotime(date('Y-m-d H:i:s')) . $validMimeTypes[$fileType[0]];
                                rename($admissionFolderPath . $fileName, $admissionFolderPath . $uniqueFileName);
    //                                Store file reference in db(admission_id, file_name, file type)
                            }
                            else{
                                $admissionFolderPath = $baseUploadPath . $admissionId . '/';
                                if(mkdir($admissionFolderPath, 0740)) {
                                    move_uploaded_file( $tempPath, $admissionFolderPath . $fileName );
                                    $uniqueFileName = CryptoLib::randomString(10) .  
                                            strtotime(date('Y-m-d H:i:s')) . $validMimeTypes[$fileType[0]];
                                    rename($admissionFolderPath . $fileName, $admissionFolderPath . $uniqueFileName);
    //                                Store file reference in db(admission_id, file_name, file type)
                                }
                            }
                        }
                        else{
                            $internalTempDirPath = $baseUploadPath . '/temp/';
                            move_uploaded_file( $tempPath, $internalTempDirPath . $fileName );
                            $uniqueFileName = CryptoLib::randomString(10) .  
                                    strtotime(date('Y-m-d H:i:s')) . $validMimeTypes[$fileType[0]];
                            rename($internalTempDirPath . $fileName, $internalTempDirPath . $uniqueFileName);
                        }
                        $serviceResult = new ServiceResult(true, $data = array("file" => $uniqueFileName), $errors = array());
                    }
                    else{
                        $errors['file'] = 'File size exceed from 20 MB';
                        $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
                    }
                }
                else{
                    $errors['file'] = 'File type not allowed';
                    $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
                }
            }
            else{
                $errors['file'] = 'File not found';
                $serviceResult = new ServiceResult(false, $data = array(), $errors = $errors);
            }

            $this->response->data = $serviceResult;

        
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
	
	
	
 
}


