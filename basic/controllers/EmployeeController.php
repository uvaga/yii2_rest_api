<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.04.2017
 * Time: 0:42
 */

namespace app\controllers;
use app\models\Employee;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use app\models\Company;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;


class EmployeeController extends ActiveController
{
    public $modelClass = 'app\models\Employee';

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
     *
     * Action get employee belongs to company
     *
     * @return ActiveDataProvider
     */
    public function actionFilterByCompany()
    {
        $company = Company::getCompany();

        return new ActiveDataProvider(['query' => $company->getEmployees(),]);
    }


    /**
     *
     * Action set employee to company
     *
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionAddEmployeeToCompany()
    {
        $employee = Employee::getEmployee();
        $company = Company::getCompany();

        if ($employee->company_id > 0 && $company->id != $employee->company_id)
            throw new BadRequestHttpException('Employee ID already belongs to another Company');

        return $employee->addToCompany($company);
    }


    /**
     *
     * Action get Employee model that belongs to company
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionViewCompanyEmployee()
    {
        $company = Company::getCompany();
        $employee = Employee::getEmployee($company->id);

        if ($employee->company_id != $company->id)
            throw new NotFoundHttpException('Employee is not found at specified Company');

        return $employee;
    }


    /**
     *
     * Action delete Employee from company
     *
     * @return null
     * @throws NotFoundHttpException
     */
    public function actionDeleteCompanyEmployee()
    {
        $employee = Employee::getEmployee();
        $company = Company::getCompany();

        if ($employee->company_id != $company->id)
            throw new NotFoundHttpException('Employee is not found at specified Company');

        $employee->deleteFromCompany();

        return null;
    }


    /**
     * Action for view Employee model's data
     * @return mixed
     */
    public function actionView()
    {
        $datas = Employee::getEmployee(true);

        return $datas;
    }


    /**
     * Action for view list of Employees
     * @return mixed
     */
    public function actionIndex()
    {
        return Employee::getModelListCache(Employee::className());
    }


    /**
     *
     * actions after view list of Employee models
     * for save in cache list of Employees
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