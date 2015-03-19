<?php

namespace app\modules\api\models;

class AppEnums{
    
    public static function getCategoryText($categoryCode){
        $categoryText = array("AS" => "App Users",
                               "CC" => "Clinic",
                                "ET" =>  "Emergency Department",
                                "FT" => "Free Standing Emergency Department",
                                "HL" => "Hospital",
                                "HR" => "Healthcare Center" );
        
        return $categoryText[strtoupper($categoryCode)];
    }
    
    public static function getRoleText($roleCode){
        
       $roleText = array( "SR" => "Sub administrator",
                           "AR" => "Administrator",
                           "QT" => "Quicare Support",
                           "RE" => "Registered Nurse",
                           "PT" => "Physician Assistant",
                           "PN" => "Physician",
                           "SF" => "Staff",
                           "SN" => "Self-accepting Physician",
                           "BR" => "Bed Flow Coordinator",
                           "AK" => "Admission Desk User",
                           "UR" => "User");
        
        return $roleText[strtoupper($roleCode)];
    }
    
    public static function getFacilityText($facilityCode){
        $facilityText = array("CC" => "Clinic",
                              "ET" => "Emergency department",
                              "FT" => "Free standing emergency department",
                              "HL" => "Hospital"
            );
        
        return $facilityText[strtoupper($facilityCode)];
    }
    
}

