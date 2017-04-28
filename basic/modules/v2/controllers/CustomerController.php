<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 20.04.2017
 * Time: 0:27
 */

namespace app\modules\v2\controllers;

use app\modules\v2\traits\SearchModelsTrait;


class CustomerController extends \app\controllers\CustomerController
{
    public $modelClass = 'app\modules\v2\models\Customer';

    use SearchModelsTrait;
}