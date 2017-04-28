<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 13:24
 */

namespace app\common\components\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;


/**
 * MyCacheBehavior is class implements methods for caching model's data.
 *
 */
class MyCacheBehavior extends Behavior
{
    /**
     * The cache component to use.
     *
     * @var string
     */
    public $cache = 'cache';

    /**
     * List of event to trigger cache's actions
     * @return array
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'clearCacheEvent',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'clearCacheEvent',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'clearCacheEvent',
        ];
    }

    /**
     * Method to clear cache after any changes in model
     * @param Event $event
     * @return bool
     */
    public function clearCacheEvent($event)
    {
        $owner = $this->owner;
        $key = $this->getCacheKey($owner->className(), $owner->id);
        $this->clearCacheCollection();
        return Yii::$app->cache->delete($key);
    }

    /**
     * Method to clear cache of model's list after changes in model
     * @return bool
     */
    public function clearCacheCollection()
    {
        $owner = $this->owner;
        return Yii::$app->cache->delete($owner->className()."List");
    }

    /**
     * Method to save data $value in cache with key $key
     *
     * @param string $key
     * @param $value
     * @return bool
     */
    public static function setCache($key, $value)
    {
        return Yii::$app->cache->set($key, $value);
    }

    /**
     * Method to get data from cache with key $key
     * @param string $key
     * @return mixed
     */
    public static function getCache($key)
    {
        return Yii::$app->cache->get($key);
    }

    /**
     * Create cache key from model's Class Name and model's ID
     *
     * @param string $class_name
     * @param null $id
     * @return string
     */
    public static function getCacheKey($class_name, $id = null)
    {
        return $class_name.':'.$id;
    }


}