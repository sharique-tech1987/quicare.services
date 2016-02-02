<?php

    return [
        'class' => 'yii\rest\UrlRule', 
        'controller' => ['api/v1/admission-report'], 
        'tokens' => [
                '{id}' => '<id:\d+>'
            ],
    ];

