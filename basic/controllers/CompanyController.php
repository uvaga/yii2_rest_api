<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 18.04.2017
 * Time: 14:37
 */

namespace app\controllers;
use yii\console\controllers\CacheController;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use app\models\Company;


class CompanyController extends ActiveController
{
    public $modelClass = 'app\models\Company';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     * Unset parent actions "view" and "index"
     * for redeclare it in current class
     *
     * @return array
     */
    public function actions(){
        $actions = parent::actions();

        unset($actions['view']);
        unset($actions['index']);

        return $actions;
    }

    /**
     * Set behaviors for class
     * corsFilter - allow access to API from external domains
     * contentNegotiator - set response format to JSON
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter' ] = [
            'class' => \yii\filters\Cors::className(),
        ];

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    /**
     * Action for view Company model data
     * @return mixed
     */
    public function actionView()
    {
        $datas = Company::getCompany(true);

        return $datas;
    }

    /**
     * action for view list of Company models
     * @return bool|mixed|ActiveDataProvider
     */
    public function actionIndex()
    {
        return Company::getModelListCache(Company::className());
    }


    /**
     *
     * actions after view list of Company models
     * for save in cache list of Companies
     *
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        if ($action->id == 'index') {
            if (\Yii::$app->request->queryString == '')
                if (\Yii::$app->cache->exists($this->modelClass.'List') == false) {
                    $result = parent::afterAction($action, $result);
                    \Yii::$app->cache->set($this->modelClass.'List', $result);
                    return $result;
                }
        }

        return parent::afterAction($action, $result);
    }


}