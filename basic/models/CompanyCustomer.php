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
use yii\web\NotFoundHttpException;
use app\common\components\behaviors\ChangeLogBehavior;


/**
 * CompanyCustomer is the model class for relation between companies and customers.
 *
 */
class CompanyCustomer extends ActiveRecord implements Linkable
{
    /**
     *
     * Behavior for logging actions with relation between companies and customers.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            ChangeLogBehavior::className(),
        ];
    }

    /**
     *
     * Set table name in BD for model
     *
     * @return string
     */
    public static function tableName()
    {
        return 'company_customers';
    }


    /**
     *
     * Get related company
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);

    }

    /**
     *
     * Get related customer
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);

    }

    /**
     *
     * Validation rules for model
     *
     * @return array
     */
    public function rules() {
        return [
            [['company_id','customer_id'], 'required'],
            ['customer_id', 'integer'],
            ['company_id', 'integer'],
        ];
    }

    /**
     *
     * Get customer's data for compnay
     *
     * @return array|null|ActiveRecord
     * @throws NotFoundHttpException
     */
    public static function getCompanyCustomer()
    {
        $request = \Yii::$app->request;
        $companyCustomer = self::find()->where(["company_id" => (int)$request->get("company_id",0),
            "customer_id" => (int)$request->get('customer_id',0)])->one();
        if (null == $companyCustomer)
            throw new NotFoundHttpException('Customer is not found at this company.');
        else
            return $companyCustomer;
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
            Link::REL_SELF => Url::to(['customer/view', 'id' => $this->id], true),
            'company' => Url::to(['company/view', 'id' => $this->company_id], true),
        ];
    }


}