<?php

namespace app\modules\api\models;

class UserMenu{
    
    public static function getUserMenu($category, $role){
//      Replace category and role with User object
        
        $checkCategory = array("CC", "FT", "ET");
        
        if(strtoupper($category) === "AS"){
            return self::getAppUserMenu($role);
        }
        else if(strtoupper($category) === "HR"){
            return self::getHealthCareCenterMenu($role);
        }
        else if(strtoupper($category) === "HL"){
            return self::getHospitalMenu($role);
        }
        else if(in_array(strtoupper($category), $checkCategory) ){
            return self::getClinicMenu($role);
        }
    }
    
    private static function getAppUserMenu($role){
//      This menu is currently same for all App Users
        $appUserMenu = array( 
                            array("name" => "Dashboard", "url" => "dashboard", "icon"=> "fa fa-dashboard"), 
                            array("name" => "Admissions", "url" => "admissions", "icon"=> "fa fa-ticket"),
                            array("name" => "Users", "url" => "users", "icon"=> "fa fa-user-md"), 
                            array("name" => "Healthcare Facilities", "url" => "facilities", "icon"=> "fa fa-h-square"),
                            array("name" => "Hospital Groups", "url" => "groups", "icon"=> "fa fa-group"), 
                                         
                                    );
        
        
            array_push($appUserMenu, array("name" => "Settings", "url" => "", "icon"=> "", 
                "childs" => array(array("name" => "Quicare Scheduler", "url" => "", "icon"=> ""),
                                  array("name" => "Account Settings", "url" => "pages/profile", "icon"=> "fa fa-user"),
//  Transaction menu will be present near future                    
//                                  array("name" => "Transactions", "url" => "", "icon"=> ""),
                                  array("name" => "Activity Log", "url" => "", "icon"=> ""),
                                  array("name" => "Support", "url" => "", "icon"=> ""),
                                  array("name" => "Help", "url" => "", "icon"=> "") ) ));
            
            return $appUserMenu;

    }
    
    private static function getHealthCareCenterMenu($role){
        $healthCareCenterMenu = array( 
                                        array("name" => "Admissions", "url" => "admissions", "icon"=> "fa fa-ticket"),
                                        array("name" => "Transfer Form", "url" => "transfer_form", "icon"=> "fa fa-file-text"), 
                                        array("name" => "Settings", "url" => "", "icon"=> "", 
                    "childs" => array(
                    array("name" => "Account Settings", "url" => "pages/profile", "icon"=> "fa fa-user"),
                    array("name" => "Support", "url" => "", "icon"=> ""),
                    array("name" => "Help", "url" => "", "icon"=> "")    ) )
                                         
                                        ); 
        
        if(strtoupper($role) === "AR"){
            array_splice($healthCareCenterMenu, 0, 0, 
                    array(array("name" => "Dashboard", "url" => "dashboard", "icon"=> "fa fa-dashboard")) );
            array_splice($healthCareCenterMenu, 3, 0, 
                    array(array("name" => "Users", "url" => "users", "icon"=> "fa fa-user-md"), 
                          array("name" => "Healthcare Facilities", "url" => "facilities", "icon"=> "fa fa-h-square"),
                          array("name" => "Hospital Groups", "url" => "groups", "icon"=> "fa fa-group")) );
            
            return $healthCareCenterMenu;
        }
        else if(strtoupper($role) === "HR"){
            return $healthCareCenterMenu;
        }
        
        return null;
    }
    
    private static function getHospitalMenu($role){
        $checkRoles = array("BR", "AK");
        
        $settingsChilds = array(
                    array("name" => "Account Settings", "url" => "pages/profile", "icon"=> "fa fa-user"),
                    array("name" => "Support", "url" => "", "icon"=> ""),
                    array("name" => "Help", "url" => "", "icon"=> ""));
        $hospitalMenu = array( 
                                        array("name" => "Admissions", "url" => "admissions", "icon"=> "fa fa-ticket"),
                                        array("name" => "Settings", "url" => "", "icon"=> "", 
                    "childs" => $settingsChilds )); 
        
        if(strtoupper($role) === "AR"){
            array_splice($hospitalMenu, 0, 0, 
                    array(array("name" => "Dashboard", "url" => "dashboard", "icon"=> "fa fa-dashboard")) );
            array_splice($hospitalMenu, 2, 0, 
                    array(array("name" => "Users", "url" => "users", "icon"=> "fa fa-user-md"),
                          array("name" => "Hospital Groups", "url" => "groups", "icon"=> "fa fa-group") ) );
            array_splice($settingsChilds, 0, 0, 
                    array(array("name" => "Quicare Scheduler", "url" => "", "icon"=> "")) );
            
            return $hospitalMenu;
        }
        else if(strtoupper($role) === "PN"){
//            Check if user is group admin then add Users in main menu and
//            Quicare Scheduler in setting's child menu
            return $hospitalMenu;
            
        }
        else if( in_array(strtoupper($role), $checkRoles)){
            return $hospitalMenu;
        }
        
        return null;
        
    }
    
    private static function getClinicMenu($role){
        /*
         * These menus are applicable on clinic type facilities (Clinic, FSED, ED)
         */
        $checkRoles = array("PN", "RE", "PT", "SF");
        
        $settingsChilds = array(
                    array("name" => "Account Settings", "url" => "pages/profile", "icon"=> "fa fa-user"),
                    array("name" => "Support", "url" => "", "icon"=> ""),
                    array("name" => "Help", "url" => "", "icon"=> ""));
        $clinicMenu = array( 
                                        array("name" => "Admissions", "url" => "admissions", "icon"=> "fa fa-ticket"),
                                        array("name" => "Settings", "url" => "", "icon"=> "", 
                    "childs" => $settingsChilds )); 
        
        if(strtoupper($role) === "AR"){
            array_splice($clinicMenu, 0, 0, 
                    array(array("name" => "Dashboard", "url" => "dashboard", "icon"=> "fa fa-dashboard")) );
            array_splice($clinicMenu, 2, 0, 
                    array(array("name" => "Users", "url" => "users", "icon"=> "fa fa-user-md") ) );
            return $clinicMenu;
        }
        else if( in_array(strtoupper($role), $checkRoles)){
            array_splice($clinicMenu, 1, 0, 
                    array(array("name" => "Transfer Form", "url" => "transfer_form", "icon"=> "fa fa-file-text")) );
            return $clinicMenu;
            
        }
        else if(strtoupper($role) === "SN"){
//            Check if user is group admin then add Users in main menu and
//            Quicare Scheduler in setting's child menu
            array_splice($clinicMenu, 1, 0, 
                    array(array("name" => "Transfer Form", "url" => "transfer_form", "icon"=> "fa fa-file-text")) );
            return $clinicMenu;
        }
        
        return null;
        
    }
    
    
}

