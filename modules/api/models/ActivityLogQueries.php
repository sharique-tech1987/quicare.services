<?php

namespace app\modules\api\models;

use Yii;
use yii\db\Query;
use app\modules\api\models\RecordFilter;

class ActivityLogQueries {
    static function insertActivity($userId, $actionId, $clientIp, $requestedUrl, $urlParams){
        $db = Yii::$app->db;
        $command = $db->createCommand()->insert('activity_log', 
                                    ["user_id" => $userId, 
                                    "action" => $actionId, 
                                    "created_on" => date("Y-m-d H:i:s", time()),
                                    "client_ip" => $clientIp,
                                    "request_url" => $requestedUrl,
                                    "request_params" => var_export($urlParams, true)]);
        $retVal = $command->execute();
        
    }
    
    static function getActivityLogs(RecordFilter $recordFilter,  $actionId = null){
        $query = (new Query())
                ->select(['al.*',
                    'ala.*',
                    'u.user_name',
                    'u.first_name',
                    'u.last_name'])
                ->from(['activity_log_actions ala'])
                ->innerJoin('activity_log al', 'al.action = ala.value')
                ->innerJoin('user u', 'u.id = al.user_id');
        
        $record_count = $query->count();
        if($actionId != null){
//            Make search_by and search_type variables 
//            and check data according to their options
            $query->where(['al.actionId' => $actionId]);
            $record_count = $query->count();
        }
        self::addOffsetAndLimit($query, $recordFilter->page, $recordFilter->limit);
        self::addSortFilter($query, $recordFilter->orderby, $recordFilter->sort);
        
        
        $rows = $query->all();
        $data = array("total_records" => $record_count, "records" => $rows);
        return $data;
    }
    
    private static function addOffsetAndLimit($query, $page, $limit){
        if(isset($page) && isset($limit)){
            $offset = $limit * ($page-1);
            $query->offset($offset)->limit($limit);
        }
        else{
            $query->offset(0)->limit(10);
        }
    }
    
    private static function addSortFilter($query, $orderby, $sort){
        $activityLogTableCols = ['action', 'created_on', 'client_ip'];
        
        if( !(isset($orderby) && isset($sort)) || (!in_array($orderby, $activityLogTableCols))  ) {
            $orderby = 'al.created_on';
            $sort = SORT_DESC;
        }
        else{
            $orderby = 'al.' . $orderby;
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