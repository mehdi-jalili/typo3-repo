<?php

defined('TYPO3') or die();

call_user_func(function () {
    $visitors = [
        'visitors' => [
            'label' => 'Visitors',
            'displayCond' => 'FIELD:is_siteroot:=:1',
            'config' => [
                'type' => 'number',
                'eval' => 'trim,int'
            ]
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'pages',
        $visitors
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'seo',
        'visitors'
    );
    if (!isset($GLOBALS['TCA']['pages']['columns']['uid'])) {
        $GLOBALS['TCA']['pages']['columns']['uid'] = [
            'config' => [
                'type' => 'passthrough',
            ],
        ];
    }
});
