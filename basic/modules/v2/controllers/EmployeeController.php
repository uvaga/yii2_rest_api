<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.04.2017
 * Time: 0:42
 */

namespace app\modules\v2\controllers;

use app\modules\v2\traits\SearchModelsTrait;



class EmployeeController extends \app\controllers\EmployeeController
{
    public $modelClass = 'app\modules\v2\models\Employee';

    use SearchModelsTrait;
}