<?php

namespace app\modules\api\models;

use Yii;
use yii\db\Query;
use app\modules\api\models\RecordFilter;

class ActivityLogQueries {
    static function insertActivity($userId, $actionId, $clientIp, $requestedUrl, $urlParams, $details){
        $db = Yii::$app->db;
        $command = $db->createCommand()->insert('activity_log', 
                                    ["user_id" => $userId, 
                                    "action" => $actionId, 
                                    "created_on" => date("Y-m-d H:i:s", time()),
                                    "client_ip" => $clientIp,
                                    "request_url" => $requestedUrl,
                                    "request_params" => var_export($urlParams, true),
                                    "details" => $details]);
        $retVal = $command->execute();
        
    }
    
    static function getActivityLogsQuery(RecordFilter $recordFilter,  $searchBy = null , $searchText = null){
        $query = (new Query())
                ->select(['al.*',
                    'ala.*',
                    'u.user_name',
                    'u.first_name',
                    'u.last_name'])
                ->from(['activity_log_actions ala'])
                ->innerJoin('activity_log al', 'al.action = ala.value')
                ->leftJoin('user u', 'u.id = al.user_id');
        
        if($searchBy != null && $searchText != null){
            if($searchBy == "u_name"){
                $query->where("[[u.user_name]] LIKE :name");
                $query->addParams([":name" => "%{$searchText}%"]);
            }
            else if($searchBy == "u_ip"){
                $query->where("[[al.client_ip]] LIKE :ip");
                $query->addParams([":ip" => "%{$searchText}%"]);
            }
            else if($searchBy == "u_action"){
                $query->where(['al.action' => $searchText]);
            }
        }
        
        return $query;
    }
    
    public static function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
        else{
            $query->offset(0)->limit(10);
        }
    }
    
    public static function addSortFilter($query, $orderby, $sort){
        $activityLogTableCols = ['action', 'created_on', 'client_ip'];
        
        if( !(isset($orderby) && isset($sort)) || (!in_array($orderby, $activityLogTableCols))  ) {
            $orderby = 'al.created_on';
            $sort = SORT_DESC;
        }
        else{
            if($orderby == "action"){
                $orderby = 'ala.name' ;
            }
            else{
                $orderby = 'al.' . $orderby;
            }
            $sort = strtoupper($sort) === 'ASC' ? SORT_ASC : SORT_DESC;
        }
        $query->orderBy([$orderby => $sort]);

    }
    
    static function getActivityLogActions(){
        $query = (new Query())
                ->select(['ala.*'])
                ->from(['activity_log_actions ala']);
        
        $rows = $query->all();
        $data = array("records" => $rows);
        return $data;
    }
    
}