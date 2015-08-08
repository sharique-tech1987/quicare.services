<?php

namespace app\modules\api\models;

abstract class Status{
    const initiated = 1;
    const accepted = 2;
    const denied = 3;
    const bedAllocated = 4;
    const patientArrived = 5;
    const patientNoShow = 6;
    const closed = 7;
    const patientDischarged = 8;
}

class AppEnums{
    
    public static function getStatusArray(){
        return array(Status::initiated, 
                    Status::accepted,
                    Status::denied,
                    Status::bedAllocated,
                    Status::patientArrived,
                    Status::patientNoShow,
                    Status::closed,
                    Status::patientDischarged);
    }
    
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
    
    public static function getTranportationText($code){
        $transportationText = array(1 => "Ambulance",
                              2 => "Personal Transportation",
                              3 => "Unknown",
                              
            );
        
        return $transportationText[strtoupper($code)];
    }
    
    public static function getBedTypeText($code){
        $bedTypeText = array(1 => "Outpatient/ Observation 24H w Telemetry",
                            2 => "Outpatient/ Observation 24H w/o Telemetry",
                            3 => "Med/Surg  w Telemetry",
                            4 => "Med/Surg w/o Telemetry",
                            5 => "Intermediate Medical Unit",
                            6 => "Intensive Care Unit",
                            7 => "Pediatrics Bed",
            );
        
        return $bedTypeText[strtoupper($code)];
    }
    
    public static function getCodeSatusText($code){
        $codeStatusText = array(1 => "Full Code",
                              2 => "Do NOT Resuscitate",
                              3 => "Comfort Measures Only",
                              
            );
        
        return $codeStatusText[strtoupper($code)];
    }
    
}



