<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 28.04.2017
 * Time: 2:41
 */

namespace app\modules\v2\traits;

use yii\db\Exception;
use yii\web\ServerErrorHttpException;

trait SearchModelsTrait
{
    public function actionSearch()
    {
        $qParams = \Yii::$app->request->getQueryParams();
        $model = new $this->modelClass;

        try
        {
            $query = $model->find()->where($qParams)->all();
        }
        catch (Exception $exception)
        {
            throw new ServerErrorHttpException("Error while executing query or bad params in request");
        }

        return $query;
    }
}