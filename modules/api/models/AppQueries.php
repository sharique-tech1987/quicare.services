<?php

namespace app\modules\api\models;

use yii\db\Query;

class AppQueries {
    
    static function getHospitalGroupsQuery(){
        /*
         * Get distinct groups query which are taken by hospitals
         */
        
        return (new Query())->select(['hcgf.group_id'])
              ->distinct()
              ->from(['health_care_facility_group hcgf'])
              ->innerJoin(['health_care_facility hcf'], '`hcgf`.`facility_id` = `hcf`.`id`')
              ->where(['hcf.type' => 'HL']);
    }
    
    static function getIcdNineByCodes($code){
        $query = (new \yii\db\Query())
        ->select(["code", "CONCAT_WS(' : ', code, long_desc) AS long_desc"])
        ->from('icd_nine_codes')
        ->where("[[code]] LIKE :search_text")
        ->limit(15);
        $query->addParams([":search_text" => "%{$code}%"]);
        $rows = $query->all();
        return $rows;
    }

    
    static function getIcdNineCodesByDescription($desc){
        $query = (new \yii\db\Query())
        ->select(["code", "CONCAT_WS(' : ', code, long_desc) AS long_desc"])
        ->from('icd_nine_codes')
        ->where("[[long_desc]] LIKE :search_text")
        ->limit(15);
        $query->addParams([":search_text" => "%{$desc}%"]);
        $rows = $query->all();
        return $rows;
    }
    
    static function getIcdTenByCodes($code){
        $query = (new \yii\db\Query())
        ->select(["code", "CONCAT_WS(' : ', code, long_desc) AS long_desc"])
        ->from('icd_ten_codes')
        ->where("[[code]] LIKE :search_text")
        ->limit(15);
        $query->addParams([":search_text" => "%{$code}%"]);
        $rows = $query->all();
        return $rows;
    }
    
    static function getIcdTenCodesByDescription($desc){
        $query = (new \yii\db\Query())
        ->select(["code", "CONCAT_WS(' : ', code, long_desc) AS long_desc"])
        ->from('icd_ten_codes')
        ->where("[[long_desc]] LIKE :search_text")
        ->limit(15);
        $query->addParams([":search_text" => "%{$desc}%"]);
        $rows = $query->all();
        return $rows;
    }
    
    static function isValidIcdCode($code){
        $row = self::isValidIcdNineCode($code);
        
        if(!$row){
            $row = self::isValidIcdTenCode($code);
            return $row;
        }
        else{
            return $row;
        }
    }
    
    private static function isValidIcdNineCode($code){
        $query = (new \yii\db\Query())
        ->select(["1"])
        ->from('icd_nine_codes')
        ->where(["code" => $code]);
        return $query->exists();
    }    
    
    private static function isValidIcdTenCode($code){
        $query = (new \yii\db\Query())
        ->select(["1"])
        ->from('icd_ten_codes')
        ->where(["code" => $code]);
        return $query->exists();
    }
}
