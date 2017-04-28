<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 26.04.2017
 * Time: 0:23
 */
use app\models\Company;
$model = new Company();

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'name',
        'address'
    ],
]);
echo cranky4\ChangeLogBehavior\ChangeLogList::widget([
    'model' => $model,
]);
?>