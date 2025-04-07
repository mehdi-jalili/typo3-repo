<?php

namespace CodingMs\ViewStatistics\Middleware;

use CodingMs\Modules\Domain\Repository\Traits\Typo3Version13Trait;
use CodingMs\ViewStatistics\Domain\Session\SessionHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Class CheckDataSubmissionMiddleware
 */
class CheckDataSubmissionMiddleware implements MiddlewareInterface
{
    use Typo3Version13Trait;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws SiteNotFoundException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //
        // Don't track anything, when you're a logged in backend user!
        if (isset($GLOBALS['BE_USER']) || ApplicationType::fromRequest($request)->isBackend()) {
            return $handler->handle($request);
        }

        if (!($request->getAttributes()['site'] instanceof Site)) {
            return $handler->handle($request);
        }
        /** @var Site $site */
        $site = $request->getAttributes()['site'];
        $this->increasePageVisitors($site);
        //
        // Get configuration
        $extensionConfiguration = $this->getExtensionConfiguration();
        $trackUser = $extensionConfiguration['track']['trackUser'];
        $trackLoggedInUserData = (bool)$extensionConfiguration['track']['trackLoggedInUserData'];
        $trackUserAgent = (bool)$extensionConfiguration['track']['userAgent'];
        $trackLoginDuration = (bool)$extensionConfiguration['track']['loginDuration'];
        //
        // Get the current page
        $pageUid = (int)$GLOBALS['TSFE']->id;
        //
        // Identify logged in user
        $frontendUserUid = 0;
        $loginDuration = 0;
        /** @var ?FrontendUserAuthentication $frontendUserAuthentication */
        $frontendUserAuthentication = $request->getAttribute('frontend.user');
        if ($frontendUserAuthentication instanceof FrontendUserAuthentication) {
            $frontendUserUid = (int)($frontendUserAuthentication->user['uid'] ?? 0);
        }
        //
        // Login duration
        if ($frontendUserUid && $trackLoginDuration) {
            $loginDuration = time() - $frontendUserAuthentication->user['lastlogin'];
            $this->updateLoginDuration($frontendUserUid, $loginDuration);
        }
        //
        // Collect tracking information
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        $sys_language_uid = $languageAspect->getId();
        $fields = [
            'frontend_user' => ($trackLoggedInUserData) ? $frontendUserUid : 0,
            'page' => $pageUid,
            'login_duration' => ($trackLoginDuration) ? $loginDuration : 0,
            'referrer' => GeneralUtility::getIndpEnv('HTTP_REFERER'),
            'request_uri' => GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            'user_agent' => ($trackUserAgent) ? GeneralUtility::getIndpEnv('HTTP_USER_AGENT') : '',
            'language' => $sys_language_uid,
            'root_page' => $site->getRootPageId()
        ];


        $loginType = trim($request->getParsedBody()['logintype'] ?? '');


        //
        // Track logout
        //if (GeneralUtility::_GP('logintype') === 'logout') {
        // Track Logout
        // ..is not possible, because the frontend user is already unset
        // when we start tracking this!
        //$this->trackLogout($fields);
        //}
        //
        // Track logged in user only?!
        // -> Exit in case of no login available
        if ($trackUser === 'loggedInOnly' && $frontendUserUid === 0) {
            return $handler->handle($request);
        }
        //
        // Track only anonym user
        // -> Exit in case of login available
        if ($trackUser === 'nonLoggedInOnly' && $frontendUserUid > 0) {
            return $handler->handle($request);
        }
        //
        // Track an object
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $configurationTypeSettings = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS;
        $configuration = $configurationManager->getConfiguration($configurationTypeSettings, 'ViewStatistics');
        if (isset($configuration['objects']) && is_array($configuration['objects'])) {
            foreach ($configuration['objects'] as $arrayKey => $object) {
                $routingArguments = $request->getAttribute('routing');
                if ($routingArguments instanceof PageArguments) {
                    $objectArray = $routingArguments->getArguments();

                    if (isset($objectArray[$arrayKey]) && is_array($objectArray[$arrayKey])) {
                        foreach ($configuration['objects'][$arrayKey] as $valueKey => $valueConfiguration) {
                            $objectUid = (int)($objectArray[$arrayKey][$valueKey] ?? 0);
                            if ($objectUid > 0) {
                                $fields['object_uid'] = $objectUid;
                                $fields['object_type'] = $valueConfiguration['table'];
                            }
                        }
                    }
                }
            }
        }
        //
        // Track user login/logout/page view
        if ($loginType === 'login') {
            // Track Login
            $this->trackLogin($fields, $extensionConfiguration);
        } else {
            // Track page view
            $this->trackPageview($fields, $extensionConfiguration);
        }
        return $handler->handle($request);
    }

    /**
     * @return array
     */
    public function getExtensionConfiguration()
    {
        // Get configuration
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfigurationUtility = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extensionConfiguration = [];
        $extensionConfiguration['track']['trackUser'] = 'all';
        $extensionConfiguration['track']['trackLoggedInUserData'] = false;
        $extensionConfiguration['track']['userAgent'] = false;
        $extensionConfiguration['track']['loginDuration'] = false;
        $extensionConfiguration['track']['trackIpAddress'] = false;
        try {
            $extensionConfiguration = (array)$extensionConfigurationUtility->get('view_statistics');
        } catch (ExtensionConfigurationExtensionNotConfiguredException $e) {
        } catch (ExtensionConfigurationPathDoesNotExistException $e) {
        }
        return $extensionConfiguration;
    }

    /**
     * @param int $frontendUser
     * @param int $loginDuration
     */
    protected function updateLoginDuration(int $frontendUser, int $loginDuration): void
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->update('tx_viewstatistics_domain_model_track')
            ->where(
                $queryBuilder->expr()->eq(
                    'frontend_user',
                    $queryBuilder->createNamedParameter($frontendUser, self::getPdoIntParam())
                )
            )
            ->andWhere('action="login"')
            ->set('login_duration', $loginDuration)
            ->orderBy('crdate')
            ->setMaxResults(1)
            ->executeQuery();
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogin(array $fields, array $configuration): void
    {
        $fields['action'] = 'login';
        if ($configuration['track']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackPageview(array $fields, array $configuration): void
    {
        $fields['action'] = 'pageview';
        if ($configuration['track']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     * @param array $configuration
     */
    protected function trackLogout(array $fields, array $configuration): void
    {
        $fields['action'] = 'logout';
        if ($configuration['track']['trackIpAddress']) {
            $fields['ip_address'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }
        $fields['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        $fields['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        $this->insertRecord($fields);
    }

    /**
     * @param array $fields
     */
    protected function insertRecord(array $fields): void
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->insert('tx_viewstatistics_domain_model_track')
            ->values($fields)
            ->executeQuery();
    }

    /**
     * @param Site $site
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    protected function increasePageVisitors(Site $site): void
    {

        /**@var SessionHandler $sessionHandler*/
        $sessionHandler =  GeneralUtility::makeInstance(SessionHandler::class);
        $session = $sessionHandler->restoreFromSession();
        if (isset($session['visited'])) {
            return;
        }
        $session['visited'] = 1;
        $sessionHandler->writeToSession($session);
        /**@var Connection $connection*/
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $queryBuilder = $connection->createQueryBuilder();
        $visitorsQuery = $queryBuilder->select('visitors')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $site->getRootPageId()))
            ->setMaxResults(1)
            ->executeQuery();
        $visitors = $visitorsQuery->fetchAssociative()['visitors'];

        $queryBuilder = $connection->createQueryBuilder();
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $site->getRootPageId())
            )
            ->set('visitors', $visitors + 1)
            ->executeQuery();
    }
}
