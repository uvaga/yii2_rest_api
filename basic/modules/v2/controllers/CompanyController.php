<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 18.04.2017
 * Time: 14:37
 */

namespace app\modules\v2\controllers;

use app\modules\v2\traits\SearchModelsTrait;


class CompanyController extends \app\controllers\CompanyController
{
    public $modelClass = 'app\modules\v2\models\Company';

    use SearchModelsTrait;

}