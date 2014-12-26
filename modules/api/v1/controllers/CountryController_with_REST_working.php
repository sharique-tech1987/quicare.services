<?php
namespace app\modules\api\v1\controllers;

use yii\rest\ActiveController;

class CountryController extends ActiveController
{
    public $modelClass = 'app\modules\api\v1\models\Country';
	
	/*public function actions()
	{
		$actions = parent::actions();
	
		// disable the "delete" and "create" actions
		//unset($actions['delete'], $actions['create']);
	
		// customize the data provider preparation with the "prepareDataProvider()" method
		$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
	
		return $actions;
	}
	
	public function prepareDataProvider()
	{
		// prepare and return a data provider for the "index" action
	}*/
}