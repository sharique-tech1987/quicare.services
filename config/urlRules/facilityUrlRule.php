<?php

    return [
        'class' => 'yii\rest\UrlRule', 
        'controller' => ['api/v1/facility'], 
        'tokens' => [
                '{id}' => '<id:\d+>'
            ]
    ];

