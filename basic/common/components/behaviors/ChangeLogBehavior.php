<?php
    /**
     * Created by PhpStorm.
     * User: Cranky4
     * Date: 28.03.2016
     * Time: 12:37
     */

namespace app\common\components\behaviors;

    use yii\base\Behavior;
    use yii\base\Event;
    use yii\data\ArrayDataProvider;
    use yii\db\ActiveRecord;

    /**
     * ChangeLogBehavior is class implements methods for logging user actions.
     *
     */
    class ChangeLogBehavior extends Behavior
    {
        public $excludedAttributes = [];
        const LogCategory = 'changelog';

        /**
         * List of event to trigger log's actions
         * @return array
         */
        public function events()
        {
            return [
                ActiveRecord::EVENT_AFTER_UPDATE => 'addUpdateLog',
                ActiveRecord::EVENT_AFTER_INSERT => 'addInsertLog',
                ActiveRecord::EVENT_BEFORE_DELETE => 'addDeleteLog',

            ];
        }


        /**
         * Logs user updated model's data
         */
        public function addUpdateLog()
        {
            $owner = $this->owner;
            $message = "User updated {$owner->className()} ID {$owner->id}";
            \Yii::info($message, self::LogCategory);
        }

        /**
         * Logs user add new model
         */
        public function addInsertLog()
        {
            $owner = $this->owner;
            $message = "User added new {$owner->className()} ID {$owner->id}";
            \Yii::info($message, self::LogCategory);
        }

        /**
         * Logs user delete model
         */
        public function addDeleteLog()
        {
            $owner = $this->owner;
            $message = "User deleted {$owner->className()} ID {$owner->id}";
            \Yii::info($message, self::LogCategory);
        }

        /**
         * Logs user viewed model's data
         * If param $company_id is correct ID of Company model - logs view model's data related to company
         * @param null $company_id
         */
        public function addViewLog($company_id = null)
        {
            $owner = $this->owner;
            if ($company_id !== null && is_numeric($company_id))
               $message = "User viewed {$owner->className()} ID {$owner->id} at Company ID {$company_id}";
            else
                $message = "User viewed {$owner->className()} ID {$owner->id}";
            \Yii::info($message, self::LogCategory);
        }

        /**
         * Logs user viewed list of models
         * If param $company_id is correct ID of Company model - logs view list of models related to company
         * @param string $className
         * @param null $company_id
         */
        public static function addViewListLog($className, $company_id = null)
        {
            if ($company_id !== null)
                $message = "User viewed list of {$className} at Company ID {$company_id}";
            else
                $message = "User viewed list of {$className}";
            \Yii::info($message, self::LogCategory);
        }

    }
