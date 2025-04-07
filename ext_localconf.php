<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ViewStatistics',
    'Visitors',
    [\CodingMs\ViewStatistics\Controller\VisitorsController::class => 'show'],
    [\CodingMs\ViewStatistics\Controller\VisitorsController::class => 'show'],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

//
// Backend TypoScript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    '@import "EXT:view_statistics/Configuration/TypoScript/Backend/setup.typoscript"'
);
