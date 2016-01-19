<?php

namespace app\modules\api\models;

class AppEnums{
    
    public static function getStatusArray(){
        return array(AppStatus::initiated, 
                    AppStatus::accepted,
                    AppStatus::denied,
                    AppStatus::bedAssigned,
                    AppStatus::patientArrived,
                    AppStatus::patientNoShow,
                    AppStatus::closed,
                    AppStatus::patientDischarged);
    }
    
    public static function getStatusConstString($status){
        $appStatusClass = new \ReflectionClass ( 'app\modules\api\models\AppStatus' );
        $constants = $appStatusClass->getConstants();
        $constName = null;
        foreach ( $constants as $name => $value )
        {
            if ( $value == $status )
            {
                    $constName = $name;
                    break;
            }
        }
        return $constName;
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
                              4 => "Air Medical Transport",
                              
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
    
    public static function getStatusIconsText($code){
        $iconsText = array(1 => "initiated_admission.png",
                              2 => "accepted_admission.png",
                              3 => "admission_denied.png",
                              4 => "bed_allocated.png",
                              5 => "patient_arrived.png",
                              6 => "closed_admission.png",
                              7 => "closed_admission.png",
                              8 => "patient_discharged.png",
                              
                              
            );
        
        return $iconsText[strtoupper($code)];
    }
    
    public static function getStatusText($code){
        $statusText = array(1 => "Initiated",
                              2 => "Accepted",
                              3 => "Denied",
                              4 => "Bed Assigned",
                              5 => "Patient Arrived",
                              6 => "Patient No-Show",
                              7 => "Closed",
                              8 => "Discharged",
                              
                              
            );
        
        return $statusText[strtoupper($code)];
    }
    
    public static function getSpecialtyText($code){
        $specialtyText = array(1 => "Anaesthesia",
                            2 => "Allergy and Immunology",
                            3 => "Cardiovascular surgery",
                            4 => "Clinical laboratory sciences",
                            5 => "Cardiology",
                            6 => "Dietetics",
                            7 => "Dermatology",
                            8 => "Emergency medicine",
                            9 => "Endocrinology",
                            10 => "Family Medicine",
                            11 => "Forensic Medicine",
                            12 => "Gynecology",
                            13 => "General surgery",
                            14 => "Geriatrics",
                            15 => "Gastroenterology",
                            16 => "Hepatology",
                            17 => "Intensive care medicine",
                            18 => "Infectious disease",
                            19 => "Medical research",
                            20 => "Neurology",
                            21 => "Neurosurgery",
                            22 => "Nephrology",
                            23 => "Otorhinolaryngology",
                            24 => "Oral and maxillofacial surgery",
                            25 => "Oncology",
                            26 => "Ophthalmology",
                            27 => "Orthopedic surgery",
                            28 => "Obstetrics and gynecology",
                            29 => "Pathology",
                            30 => "Palliative care",
                            31 => "Pediatrics",
                            32 => "Physical medicine and rehabilitation Or Physiatry",
                            33 => "Plastic surgery",
                            34 => "Pulmonology",
                            35 => "Podiatry",
                            36 => "Proctology",
                            37 => "Pediatric surgery",
                            38 => "Psychiatry",
                            39 => "Radiology",
                            40 => "Rheumatology",
                            41 => "Stomatology",
                            42 => "Surgical oncology",
                            43 => "Thoracic surgery",
                            44 => "Transplant surgery",
                            45 => "Urgent Care Medicine",
                            46 => "Urology",
                            47 => "Vascular surgery",
                        );

        return $specialtyText[strtoupper($code)];
    }
    
    public static function getRecordTypeText($code){
        $recordTypeText = array(1 => "Xray",
                              2 => "CT Scan",
                              3 => "Ultrasound",
                              4 => "ECG",
                              5 => "MRI",                              
            );
        
        return $recordTypeText[strtoupper($code)];
    }
    
}



