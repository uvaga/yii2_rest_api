<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
        ],
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'dfiugbu325ghdfgrd',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'formatters' => [
                'json' => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port' => 6379,
                'database' => 1,
            ]
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log'         => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                // add new target
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class'      => 'yii\log\DbTarget',
                    'categories' => ['changelog*'],
                    'logTable'   => '{{%changelogs}}',
                    //remove application category from logging
                    'logVars'    => [],
                    'levels'     => ['info'],
                ],
            ],
        ],

        'db' => require(__DIR__ . '/db.php'),

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET,HEAD <module>/companies/<company_id:\d+>/customers' => '<module>/customer/filter-by-company',
                'GET,HEAD <module>/companies/<company_id:\d+>/employees' => '<module>/employee/filter-by-company',
                'GET v2/companies/search' => 'v2/company/search',
                'GET v2/customers/search' => 'v2/customer/search',
                'GET v2/employees/search' => 'v2/employee/search',


                'OPTIONS <module>/companies/<company_id:\d+>/customers' => '<module>/customer/options',
                'OPTIONS <module>/companies/<company_id:\d+>/employees' => '<module>/employee/options',
                'POST <module>/companies/<company_id:\d+>/customers' => '<module>/customer/add-customer-to-company',
                'POST <module>/companies/<company_id:\d+>/employees' => '<module>/employee/add-employee-to-company',

                'GET,HEAD <module>/companies/<company_id:\d+>/employees/<employee_id:\d+>' => '<module>/employee/view-company-employee',
                'GET,HEAD <module>/companies/<company_id:\d+>/customers/<customer_id:\d+>' => '<module>/customer/view-company-customer',
                'PUT,PATCH <module>/companies/<company_id:\d+>/employees/<id:\d+>' => '<module>/employee/update',
                'PUT,PATCH <module>/companies/<company_id:\d+>/customers/<id:\d+>' => '<module>/customer/update',

                'DELETE <module>/companies/<company_id:\d+>/employees/<employee_id:\d+>' => '<module>/employee/delete-company-employee',
                'DELETE <module>/companies/<company_id:\d+>/customers/<customer_id:\d+>' => '<module>/customer/delete-company-customer',
                [
                'class' => 'yii\rest\UrlRule',
                'controller' => ['v1/company', 'v1/employee', 'v1/customer','v2/company', 'v2/employee', 'v2/customer'],
                ],
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
