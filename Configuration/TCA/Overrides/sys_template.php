<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'view_statistics',
    'Configuration/TypoScript',
    'View-Statistics'
);
