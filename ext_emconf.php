<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'av light-dark Legacy Browsers',
    'description' => 'CSS workaround for clients with legacy browsers that don\'t support light-dark() in Typo3 13 backend',
    'category' => 'be',
    'author' => 'Mehdi Jalili',
    'author_email' => 'jalilimehdi.1366@gmail.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
