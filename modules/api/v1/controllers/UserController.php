<?php
namespace app\modules\api\v1\controllers;
 
use Yii;
use app\modules\api\v1\models\User;
use yii\data\ActiveDataProvider;
//use yii\web\Controller;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
 
/**
* UserController implements the CRUD actions for User model.
*/
class UserController extends Controller
{
 
    public function behaviors()
    {
    return [
        'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
			/*'index'  => ['get'],
			'view'   => ['get'],
			'create' => ['get', 'post'],
			'update' => ['get', 'put', 'post'],
			'delete' => ['post', 'delete'],
			*/
            'index'=>['get'],
            'view'=>['get'],
            'create'=>['post'],
            'update'=>['put'],
            'delete' => ['delete'],
            'deleteall'=>['post'],
			
        ],
 
        ]
    ];
    }
 
 
    public function beforeAction($event)
    {
    $action = $event->id;
    if (isset($this->actions[$action])) {
        $verbs = $this->actions[$action];
    } elseif (isset($this->actions['*'])) {
        $verbs = $this->actions['*'];
    } else {
        return $event->isValid;
    }
    $verb = Yii::$app->getRequest()->getMethod();
 
      $allowed = array_map('strtoupper', $verbs);
 
      if (!in_array($verb, $allowed)) {
 
        $this->setHeader(400);
        echo json_encode(array('status'=>0,'error_code'=>400,'message'=>'Method not allowed'),JSON_PRETTY_PRINT);
        exit;
 
    }  
 
      return true;  
    }
 
    /**
    * Lists all User models.
    * @return mixed
    */
    public function actionIndex()
    {
 
          $params=$_REQUEST;
          $filter=array();
          $sort="";
 
          $page=1;
          $limit=10;
 
           if(isset($params['page']))
             $page=$params['page'];
 
 
           if(isset($params['limit']))
              $limit=$params['limit'];
 
            $offset=$limit*($page-1);
 
 			$query=new Query;
			$query->from('user')->select("id,name,age,createdAt,updatedAt")
			->offset($offset)
			->limit($limit)
			;
			
            /* Filter elements */
           if(isset($params['filter']))
            {
             $filter=(array)json_decode($params['filter']);
            }
 
 			
            if(isset($params['datefilter']))
            {
             $datefilter=(array)json_decode($params['datefilter']);
            }
 
 
            if(isset($params['sort']))
            {
              	$sort=$params['sort'];
				 if(isset($params['order']))
				{  
					if($params['order']=="false")
					 $sort.=" desc";
					else
					 $sort.=" asc";
		 
				}
            }
			
		$query->orderBy($sort);
 
 		
 		if(isset($filter['id'])){
			$query->andFilterWhere(['like', 'id', $filter['id']]);
		}
		
		if (isset($filter['name'])){
			$query->andFilterWhere(['like', 'name', $filter['name']]);
		}
		
		if (isset($filter['age'])){
			$query->andFilterWhere(['like', 'age', $filter['age']]);
		}
		
		if(isset($datefilter['from']))
	    {
		 $query->andWhere("createdAt >= '".$datefilter['from']."' ");
	    }
	    if(isset($datefilter['to']))
	    {
		 $query->andWhere("createdAt <= '".$datefilter['to']."'");
	    }
 
           $command = $query->createCommand();
               $models = $command->queryAll();
 
               $totalItems=$query->count();
 
          $this->setHeader(200);
 
          echo json_encode(array('status'=>1,'data'=>$models,'totalItems'=>$totalItems),JSON_PRETTY_PRINT);
 
    }
 
 
    /**
    * Displays a single User model.
    * @param integer $id
    * @return mixed
    */
    public function actionView($id)
    {
      $model=$this->findModel($id);
 
      $this->setHeader(200);
      echo json_encode(array('function'=>"in view", 'status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
 
    }
 
    /**
    * Creates a new User model.
    * @return json
    */
    public function actionCreate()
    {
 
    $params=$_REQUEST;
 
    $model = new User();
    $model->attributes=$params;
 
 
 
    if ($model->save()) {
 
        $this->setHeader(200);

        echo json_encode(array('status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
 
    } 
    else
    {
        $this->setHeader(400);
        echo json_encode(array("function"=>'in create', "params"=>$params, 'status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
    }
 
    }
 
    /**
    * Updates an existing User model.
    * @param integer $id
    * @return json
    */
	
	
    public function actionUpdate($id)
    {
    $params=$_REQUEST;
 
    $model = $this->findModel($id);
 
    $model->attributes=$params;
	
 
    if ($model->save()) {
 
        $this->setHeader(200);
        echo json_encode(array("function"=>'in update','params'=>$params, 'status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
 
    } 
    else
    {
        $this->setHeader(400);
        echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
    }
 
    }
 
    /**
    * Deletes an existing User model.
    * @param integer $id
    * @return json
    */
    public function actionDelete($id)
    {
    $model=$this->findModel($id);
 
    if($model->delete())
    { 
        $this->setHeader(200);
        echo json_encode(array('status'=>1,'data'=>array_filter($model->attributes)),JSON_PRETTY_PRINT);
 
    }
    else
    {
 
        $this->setHeader(400);
        echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
    }
 
    }
    /**
    * Deletes an existing multiple User models at a time.
    * @return json
    */
    public function actionDeleteall()
    {
    $ids=json_decode($_REQUEST['ids']);
 
    $data=array();
 
    foreach($ids as $id)
    {
      $model=$this->findModel($id);
 
      if($model->delete())
        $data[]=array_filter($model->attributes);
      else
      {
        $this->setHeader(400);
        echo json_encode(array('status'=>0,'error_code'=>400,'errors'=>$model->errors),JSON_PRETTY_PRINT);
        return;
      }  
    }
 
    $this->setHeader(200);
    echo json_encode(array('status'=>1,'data'=>$data),JSON_PRETTY_PRINT);
 
    }
 
    /**
    * Finds the User model based on its primary key value.
    * If the model is not found, a 404 HTTP exception will be thrown.
    * @param integer $id
    * @return User the loaded model
    */
    protected function findModel($id)
    {
    if (($model = User::findOne($id)) !== null) {
        return $model;
    } else {
 
      $this->setHeader(400);
      echo json_encode(array('status'=>0,'error_code'=>400,'message'=>'Bad request'),JSON_PRETTY_PRINT);
      exit;
    }
    }
 
    private function setHeader($status)
      {
 
      $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
      $content_type="application/json; charset=utf-8";
 
      header($status_header);
      header('Content-type: ' . $content_type);
      header('X-Powered-By: ' . "Nintriva <nintriva.com>");
      }
    private function _getStatusCodeMessage($status)
    {
    // these could be stored in a .ini file and loaded
    // via parse_ini_file()... however, this will suffice
    // for an example
    $codes = Array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
    );
    return (isset($codes[$status])) ? $codes[$status] : '';
    }
}