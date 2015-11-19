<?php

    return [
        'class' => 'yii\rest\UrlRule', 
        'controller' => ['api/file'], 
        'tokens' => [
            '{id}' => '<id:[0-9,]+>'
        ]
    ];

