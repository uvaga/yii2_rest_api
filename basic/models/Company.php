<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 18.04.2017
 * Time: 18:14
 */

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\common\components\behaviors\MyCacheBehavior;
use app\common\components\behaviors\ChangeLogBehavior;
use app\common\components\traits\ModelCacheTrait;


class Company extends ActiveRecord implements Linkable
{
    /**
     * Include trait implements some cache actions
     */
    use ModelCacheTrait;

    /**
     *
     * behaviors for caching model's data and logging actions with model's data
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => MyCacheBehavior::className(),
            ],
            ChangeLogBehavior::className(),

        ];
    }

    /**
     *
     * validation rules for model's data
     *
     * @return array
     */
    public function rules() {
        return [
            [['name','address'], 'required'],
            ['name', 'string', 'max' => 75],
            ['address', 'string', 'max' => 100],
        ];
    }

    /**
     *
     * Get list of Customers for company
     *
     * @return $this
     */
    public function getCustomers()
    {
        $datas = $this->hasMany(Customer::className(), ['id' => 'customer_id'])
            ->viaTable(CompanyCustomer::tableName(), ['company_id' => 'id']);
        ChangeLogBehavior::addViewListLog(Customer::className(), $this->id);
        return $datas;
    }

    /**
     *
     * Get list of Employees for company
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        $datas = $this->hasMany(Employee::className(), ['company_id' => 'id']);
        ChangeLogBehavior::addViewListLog(Employee::className(), $this->id);
        return $datas;
    }

    /**
     *
     * Method for get Company model by ID from request
     * Data gets from cache, if cache exists, or from DB
     *
     * @param bool $fromViewAction
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public static function getCompany($fromViewAction = false)
    {
        $request = \Yii::$app->request;

        if (null !== $request->get('company_id'))
            $id = (int)$request->get('company_id');
        elseif (null !== $request->post('company_id'))
            $id = (int)$request->post('company_id');
        elseif (null !== $request->get('id'))
            $id = (int)$request->get('id');
        else
            throw new BadRequestHttpException('Correct Company ID is nedeed');

        $company = self::getOrSetModelCache(self::className(), $id);
        if ($company === null)
            throw new NotFoundHttpException('Company is not found');
        if ($fromViewAction)
            $company->addViewLog();
        return $company;
    }

    /**
     * Event to delete links between Company and Customers
     * after company's deletion
     */
    public function afterDelete()
    {
        $db = \Yii::$app->db;
        $db->createCommand()
            ->delete(CompanyCustomer::tableName(), ['company_id' => $this->id])
            ->execute();

        $db->createCommand()
            ->update(Employee::tableName(), ['company_id' => 0], 'company_id = '.$this->id)
            ->execute();
    }

    /**
     *
     * Generate links to related instances
     *
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['company/view', 'id' => $this->id], true),
            'customers' => Url::to(['companies/'.$this->id.'/customers'], true),
            'employees' => Url::to(['companies/'.$this->id.'/employees'], true),

        ];
    }

}