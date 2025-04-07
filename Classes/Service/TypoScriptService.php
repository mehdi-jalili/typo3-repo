<?php

declare(strict_types=1);

namespace CodingMs\ViewStatistics\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Mehdi Jalili <typo3@coding.ms>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Exception;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\TypoScriptService as TypoScriptServiceCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Services on TypoScript
 *
 * PLEASE SYNC FIXES AND CHANGES WITH EXT:guidelines
 *
 * @author Mehdi Jalili <typo3@coding.ms>
 */
class TypoScriptService
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected static ?array $typoScript = null;

    /**
     * @param int $pageUid
     * @param int $languageUid
     * @param array $rootline
     * @param Site|null $site
     * @return array<string, mixed>
     * @throws SiteNotFoundException
     */
    public static function getTypoScript(int $pageUid, int $languageUid = 0, array $rootline = [], ?Site $site = null): array
    {
        if (!isset(self::$typoScript[$pageUid])) {
            //
            // In case of executing by console, any request url must be available!
            $requestUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
            if (is_string($requestUrl) && substr($requestUrl, 0, 8) === 'http:///') {
                GeneralUtility::setIndpEnv('TYPO3_REQUEST_URL', 'https://www.dummy.domain/');
            }
            //
            // Ensure the rootline is available
            if (count($rootline) === 0) {
                /** @var RootlineUtility $rootlineUtility */
                $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
                $rootline = $rootlineUtility->get();
            }
            //
            // Ensure the site configuration is available
            if (!($site instanceof Site)) {
                /** @var SiteFinder $siteFinder */
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                $site = $siteFinder->getSiteByPageId($pageUid);
            }
            //
            // Ensure TSFE is initialized, otherwise there might be some errors
            $unsetTSFE = false;
            if (!isset($GLOBALS['TSFE'])) {
                $unsetTSFE = true;
                $context = GeneralUtility::makeInstance(Context::class);
                $frontendUserAuthentication = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
                $pageArguments = GeneralUtility::makeInstance(PageArguments::class, $pageUid, '', []);
                $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
                    TypoScriptFrontendController::class,
                    $context,
                    $site,
                    $site->getLanguageById($languageUid),
                    $pageArguments,
                    $frontendUserAuthentication
                );
            }
            // Get TypoScript
            /** @var ConfigurationManager $configurationManager */
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $fullTyposcript = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            /** @var TypoScriptServiceCore $typoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptServiceCore::class);
            self::$typoScript[$pageUid] = $typoScriptService->convertTypoScriptArrayToPlainArray($fullTyposcript);
            if ($unsetTSFE) {
                $GLOBALS['TSFE'] = null;
            }
        }
        return self::$typoScript[$pageUid];
    }

    /**
     * @param string $basis Extension key underscored
     * @param string $pro Extension key underscored
     * @param int $pageUid Page uid if multi pages were already requested. Otherwise, the first node will be used
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    public static function getTypoScriptPluginSettingsMerged(string $basis = '', string $pro = '', int $pageUid=0): array
    {
        $basis = strtolower($basis);
        $pro = strtolower(str_replace('_', '', $pro));
        if (self::$typoScript === null) {
            throw new Exception('TypoScript not found - please run TypoScriptService::getTypoScript!');
        }
        if ($pageUid === 0) {
            $pageUid = array_key_first(self::$typoScript);
        }
        if (!isset(self::$typoScript[$pageUid]['plugin']['tx_' . $basis]['settings'])) {
            throw new Exception('Base extension \'' . $basis . '\' settings not found!');
        }
        if (!isset(self::$typoScript[$pageUid]['plugin']['tx_' . $pro]['settings'])) {
            throw new Exception('Pro extension \'' . $pro . '\' settings not found!');
        }
        return array_replace_recursive(
            self::$typoScript[$pageUid]['plugin']['tx_' . $basis]['settings'],
            self::$typoScript[$pageUid]['plugin']['tx_' . $pro]['settings']
        );
    }
}
