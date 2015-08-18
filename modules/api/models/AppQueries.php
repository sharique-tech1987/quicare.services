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
    
    static function isValidIcdCode($code, $desc){
        $row = self::isValidIcdNineCode($code, $desc);
        
        if(!$row){
            $row = self::isValidIcdTenCode($code, $desc);
            return $row;
        }
        else{
            return $row;
        }
    }
    
    private static function isValidIcdNineCode($code, $desc){
        $query = (new \yii\db\Query())
        ->select(["1"])
        ->from('icd_nine_codes')
        ->where(["code" => $code, "long_desc" => $desc]);
        return $query->exists();
    }    
    
    private static function isValidIcdTenCode($code, $desc){
        $query = (new \yii\db\Query())
        ->select(["1"])
        ->from('icd_ten_codes')
        ->where(["code" => $code, "long_desc" => $desc]);
        return $query->exists();
    }

    static function insertAdmissionStatus($db, $admissionId, $status){
        $command = $db->createCommand()->insert('admission_status', 
                                    ["admission_id" => $admissionId, 
                                    "status" => $status, 
                                    "created_on" => date("Y-m-d H:i:s", time())]);
        $command->execute();
    }
    
    public static function isValidAdmission($admissionId){
        $query = (new \yii\db\Query())
        ->select(["1"])
        ->from('admission')
        ->where(["transaction_number" => $admissionId]);
        return $query->exists();
    }
    
    public static function getLastAdmissionStatus($admissionId){
        $query = (new \yii\db\Query())
        ->select(["*"])
        ->from('admission_status')
        ->where(["admission_id" => $admissionId])
        ->orderBy(['created_on' => SORT_DESC]);
        return $query->one();
    }
    
    public static function getAdmissionStatuses($admissionId){
        $query = (new \yii\db\Query())
        ->select(["*"])
        ->from('admission_status')
        ->where(["admission_id" => $admissionId])
        ->orderBy(['created_on' => SORT_DESC]);
        return $query->all();
    }
    
    public static function getFacilitiesGroups($facilityIds){
        $query = (new Query())
                ->select(['hfg.group_id'])
                ->from('health_care_facility_group hfg')
                ->innerJoin('group g', 'hfg.group_id = g.id')
                ->where(["hfg.facility_id" => $facilityIds, "g.deactivate" => "F"]);
                
        $rows = $query->all();
        return $rows;
    }
    
    public static function isValidPhysician($value){
        $query = (new Query())
                ->select('1')
                ->from('user')
                ->where(['and', 'id= :id', ['or', ['and', "category='HL'", "role='PN'" ],  ['and', "category='CC'", "role='SN'"] ]])
                ->addParams([':id' => $value])
                ->exists();
        return $query;
    }
}
