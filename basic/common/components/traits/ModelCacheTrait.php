<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 16:40
 */

namespace app\common\components\traits;

use app\common\components\behaviors\MyCacheBehavior;
use app\common\components\behaviors\ChangeLogBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;


trait ModelCacheTrait
{
    /**
     *
     * Check if model's data exists in cache and return it
     * else get model's data from DB
     *
     * @param string $className
     * @param null $id
     * @return mixed
     */
    public function getOrSetModelCache($className, $id = null)
    {
        $key = MyCacheBehavior::getCacheKey($className, $id);
        $model = MyCacheBehavior::getCache($key);
        if ($model == false)
        {
            $model = $className::findOne($id);
            if (null !== $model)
                MyCacheBehavior::setCache($key,$model);
        }
        return $model;
    }

    /**
     * Check if list of models exists in cache and return it
     * else get models list from DB
     *
     * @param string $className
     * @return bool|mixed|ActiveDataProvider
     */
    public static function getModelListCache($className)
    {
        $cacheDatas =  false;
        if (\Yii::$app->request->queryString == '')
            $cacheDatas = \Yii::$app->cache->get($className.'List');
        ChangeLogBehavior::addViewListLog($className);
        if ($cacheDatas != false)
            return $cacheDatas;
        else
        {
            $query = new  \yii\db\ActiveQuery($className);
            $datas = new ActiveDataProvider([
                'query' => $query->from($className::tableName()),
            ]);
            return $datas;
        }
    }

}