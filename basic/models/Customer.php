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
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use app\common\components\behaviors\MyCacheBehavior;
use app\common\components\behaviors\ChangeLogBehavior;
use app\common\components\traits\ModelCacheTrait;

class Customer extends ActiveRecord implements Linkable
{

    /**
     * Include trait implements some cache actions
     */
    use ModelCacheTrait;

    /**
     *
     * Behaviors for caching model's data and logging actions with model's data
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            MyCacheBehavior::className(),
            ChangeLogBehavior::className(),
        ];
    }

    /**
     *
     * Validation rules for model's data
     *
     * @return array
     */
    public function rules() {
        return [
            [['first_name','last_name','email','address'], 'required'],
            ['first_name', 'string', 'max' => 75],
            ['last_name', 'string', 'max' => 75],
            ['email', 'email'],
            ['address', 'string', 'max' => 100],
        ];
    }


    /**
     * Get related connection to company
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasMany(CompanyCustomer::className(), ['customer_id' => 'id']);

    }

    /**
     * Get related list of companies
     *
     * @return $this
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['id' => 'company_id'])
            ->viaTable(CompanyCustomer::tableName(), ['customer_id' => 'id']);
    }

    /**
     * Get Customer model by ID from request
     * Data gets from cache, if cache exists, or from DB
     *
     * @param bool $fromViewAction
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public static function getCustomer($fromViewAction = false)
    {
        $request = \Yii::$app->request;
        if (null !== $request->get('customer_id'))
            $id = (int)$request->get('customer_id');
        elseif (null !== $request->post('customer_id'))
            $id = (int)$request->post('customer_id');
        elseif (null !== $request->get('id'))
            $id = (int)$request->get('id');
        else
            throw new BadRequestHttpException('Correct Customer ID is nedeed');
        $customer = self::getOrSetModelCache(self::className(), $id);
        if ($customer === null)
            throw new NotFoundHttpException('Customer is not found');
        if ($fromViewAction)
            $customer->addViewLog($fromViewAction);
        return $customer;
    }

    /**
     *
     * Add Customer relation to Company
     *
     * @param Company $company
     * @return $this
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function addToCompany($company)
    {
        if (CompanyCustomer::find()->where(["company_id" => $company->id, "customer_id" => $this->id])->one() !== null)
        {
            throw new BadRequestHttpException('Customer already belongs to this company.');
        }
        $companyCustomer = new CompanyCustomer();
        $companyCustomer->company_id = $company->id;
        $companyCustomer->customer_id = $this->id;

        if ($companyCustomer->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $response->getHeaders()->set('Location', Url::to(["companies/{$company->id}/customers/{$this->id}"], true));
        } elseif (!$this->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $this;
    }

    /**
     * Delete relation between Customer and Company
     *
     * @return bool
     * @throws ServerErrorHttpException
     */
    public static function deleteFromCompany()
    {
        $companyCustomer = CompanyCustomer::getCompanyCustomer();

        if (null !== $companyCustomer)
            if($companyCustomer->delete())
            {
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(204);
            } elseif (!$companyCustomer->hasErrors()) {
                throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
            }
         return true;
    }

    /**
     * Event for delete relation between Customer and Companies
     * after customer deletion
     */
    public function afterDelete()
    {
        \Yii::$app
            ->db
            ->createCommand()
            ->delete(CompanyCustomer::tableName(), ['customer_id' => $this->id])
            ->execute();
    }


    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['customer/view', 'id' => $this->id], true),
        ];
    }


}