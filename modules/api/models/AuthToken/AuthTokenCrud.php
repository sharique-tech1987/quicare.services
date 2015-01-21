<?php

namespace app\modules\api\models\AuthToken;

use app\modules\api\models\AuthToken\AuthToken;
use app\modules\api\models\ServiceResult;
use app\modules\api\components\CryptoLib;
use app\modules\api\v1\models\User\User;

class AuthTokenCrud{
    
    private static function verifyCreateParams($userName, $password){
        if(!isset($userName) || $userName == null){
            throw new  \Exception("UserName should not be null or empty");
        }
        if(!isset($password) || $password == null){
            throw new \Exception("Password should not be null or empty");
        }
    }
    
    public static function create($userName, $password){
        AuthTokenCrud::verifyCreateParams($userName, $password);
        
        $isSaved = false;
        $errors  = array();
        $user = User::getUser($userName);
        if(isset($user)){
            $salt = $user->salt;
            $hash = $user->password;
            if(CryptoLib::validateHash($hash, $password, $salt)){
                $authToken = new AuthToken();
                $authToken->scenario = "post";
                $authToken->user_id = $user->id;
                $authToken->token = CryptoLib::randomString(32);
                $isSaved = $authToken->save();
                if(!$isSaved){
                    $errors["message"] = $authToken->getErrors();
                }

            }
            else{
                $errors["message"] = "Invalid username or password";
            }
        }
        else{
            $errors["message"] = "Invalid username or password";

        }
        
        $serviceResult = null;
        
        if ($isSaved) {
            $data = array("auth_token" => $authToken->token, "user_name" => $user->user_name);
            $serviceResult = new ServiceResult(true, $data, $errors = array());
        } 
        else{
            $serviceResult = new ServiceResult(false, $data = array(), 
                $errors = $errors);
        }
        
        return $serviceResult;
    }
    
//  Use this function to update last request time
    public static function update($token){
        $isSaved = false;
        $errors = array();
        $authToken = AuthToken::findOne($token);
        if($authToken !== null ){
//          Just update field updated_on in auth token
            $isSaved = $authToken->save();
            if(!$isSaved){
                throw new \Exception("Token not updated");
            }
        }
        else{
            throw new \Exception("Token is expired");
        }   
            
    }
    
    
}

