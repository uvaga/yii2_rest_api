<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.04.2017
 * Time: 0:27
 */

namespace app\controllers;
use app\models\Company;
use app\models\CompanyCustomer;
use app\models\Customer;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;


class CustomerController extends ActiveController
{
    public $modelClass = 'app\models\Customer';

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

        $behaviors['corsFilter'] = [
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
     * Action get list of customers belongs to company
     *
     * @return ActiveDataProvider
     */
    public function actionFilterByCompany()
    {
        $company = Company::getCompany();

        return new ActiveDataProvider(['query' => $company->getCustomers(),
        ]);
    }


    /**
     *
     * Action set customer to company
     *
     * @return ActiveDataProvider
     */
    public function actionAddCustomerToCompany()
    {
        $customer = Customer::getCustomer();
        $company = Company::getCompany();
        $customer->addToCompany($company);

        return $customer;
    }


    /**
     *
     * Action get Customer model that belongs to company
     *
     * @return ActiveDataProvider
     */
    public function actionViewCompanyCustomer()
    {
        $customer = Customer::getCustomer((int)\Yii::$app->request->get('company_id',false));
        CompanyCustomer::getCompanyCustomer();

        return $customer;
    }

    /**
     *
     * Action delete Customer from company
     *
     * @return ActiveDataProvider
     */
    public function actionDeleteCompanyCustomer()
    {
        Customer::deleteFromCompany();
        return '';
    }

    /**
     * Action for view Customer model data
     * @return mixed
     */
    public function actionView()
    {
        $datas = Customer::getCustomer(true);

        return $datas;
    }


    /**
     * Action for view list of Customers
     * @return mixed
     */
    public function actionIndex()
    {
        return Customer::getModelListCache(Customer::className());
    }

    /**
     *
     * actions after view list of Customer models
     * for save in cache list of Customers
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