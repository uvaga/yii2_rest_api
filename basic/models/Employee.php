<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 18.04.2017
 * Time: 18:15
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

class Employee extends ActiveRecord implements Linkable
{

    /**
     * Include trait implements some cache actions
     */
    use ModelCacheTrait;

    /**
     * Behaviors for caching model's data and logging actions with model's data
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
     * Get related Compnay
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);

    }


    /**
     * Validation rules for Employee model
     * @return array
     */
    public function rules() {
        return [
            [['first_name','last_name','email','position','salary'], 'required'],
            ['first_name', 'string', 'max' => 75],
            ['last_name', 'string', 'max' => 75],
            ['email', 'email'],
            ['position', 'string', 'max' => 100],
            ['company_id', 'integer'],
            ['salary', 'number'],
        ];
    }

    /**
     * Get Employee model by ID from request
     * Data gets from cache, if cache exists, or from DB
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public static function getEmployee($fromViewAction = false)
    {
        $request = \Yii::$app->request;
        if (null !== $request->get('employee_id'))
            $id = (int)$request->get('employee_id');
        elseif (null !== $request->post('employee_id'))
            $id = (int)$request->post('employee_id');
        elseif (null !== $request->get('id'))
            $id = (int)$request->get('id');
        else
            throw new BadRequestHttpException('Correct Employee ID is nedeed');

        $employee = self::getOrSetModelCache(self::className(), $id);

        if ($employee === null)
            throw new NotFoundHttpException('Employee is not found');
        if ($fromViewAction)
            $employee->addViewLog($fromViewAction);

        return $employee;
    }

    /**
     * Delete relation between Employee and Company
     * @return bool
     * @throws ServerErrorHttpException
     */
    public function deleteFromCompany()
    {
        $this->company_id = 0;
        if ($this->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(204);
        } elseif (!$this->hasErrors()) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        return true;
    }

    /**
     * Add Employee relation to Company
     *
     * @param $company
     * @return $this
     * @throws ServerErrorHttpException
     */
    public function addToCompany($company)
    {
        $this->company_id = $company->id;
        if ($this->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $response->getHeaders()->set('Location', Url::to(["companies/{$company->id}/employees/{$this->id}"], true));
        } elseif (!$this->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['employee/view', 'id' => $this->id], true),
        ];
    }
}