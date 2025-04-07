<?php

$return['viewstatistics_viewstatistics'] = [
    'parent' => 'web',
    'position' => [],
    'access' => 'user',
    'icon' => 'EXT:view_statistics/Resources/Public/Icons/module-viewstatistics.svg',
    'iconIdentifier' => 'module-viewstatistics',
    'path' => '/module/viewstatistics/viewstatistics',
    'labels' => 'LLL:EXT:view_statistics/Resources/Private/Language/locallang_db.xlf',
    'extensionName' => 'ViewStatistics',
    'controllerActions' => [
        \CodingMs\ViewStatistics\Controller\BackendController::class => [
            'list',
            'listForUser',
            'listForPage',
            'listForObject',
            'statistic',
            'userStatistics',
            'listForSummary'
        ]
    ],
];
return $return;
