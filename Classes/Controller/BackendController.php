<?php

namespace CodingMs\ViewStatistics\Controller;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 Mehdi Jalili <typo3@coding.ms>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use CodingMs\Modules\Controller\BackendController as BaseBackendController;
use CodingMs\Modules\Domain\Model\FrontendUser;
use CodingMs\Modules\Domain\Repository\FrontendUserRepository;
use CodingMs\Modules\Utility\BackendListUtility;
use CodingMs\ViewStatistics\Domain\Repository\PageRepository;
use CodingMs\ViewStatistics\Domain\Repository\TrackRepository;
use CodingMs\ViewStatistics\Service\TypoScriptService as ViewStatisticsTypoScriptService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder as UriBuilderBackend;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use DateTime;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * BackendController
 */
class BackendController extends BaseBackendController
{

    protected TrackRepository $trackRepository;

    /**
     * @param TypoScriptService $typoScriptService
     * @param BackendListUtility $backendListUtility
     * @param UriBuilderBackend $uriBuilderBackend
     * @param ModuleTemplateFactory $moduleTemplateFactory
     * @param TrackRepository $trackRepository
     * @param FrontendUserRepository $frontendUserRepository
     * @param PageRepository $pageRepository
     */
    public function __construct(
        TypoScriptService      $typoScriptService,
        BackendListUtility     $backendListUtility,
        UriBuilderBackend      $uriBuilderBackend,
        ModuleTemplateFactory  $moduleTemplateFactory,
        TrackRepository        $trackRepository,
        FrontendUserRepository $frontendUserRepository,
        PageRepository         $pageRepository
    )
    {
        parent::__construct(
            $typoScriptService,
            $backendListUtility,
            $uriBuilderBackend,
            $moduleTemplateFactory
        );
        //
        $this->extensionName = 'ViewStatistics';
        $this->modulePrefix = 'tx_viewstatistics_viewstatistics_viewstatistics';
        $this->moduleName = 'viewstatistics_viewstatistics';
        //
        $this->trackRepository = $trackRepository;
        $this->frontendUserRepository = $frontendUserRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initializeAction(): void
    {
        parent::initializeAction();
        $this->createMenu();
        $this->createButtons();
        //
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile('EXT:modules/Resources/Public/Stylesheets/Modules.css');
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
            JavaScriptModuleInstruction::create('@codingms/view-statistics/backend/date-picker.js')->invoke('initialize')
        );
        // Fetch form configurations for current page
        $typoScript = ViewStatisticsTypoScriptService::getTypoScript($this->pageUid);
        if (isset($typoScript['plugin']['tx_viewstatistics']['settings'])) {
            $this->settings = array_merge($this->settings, $typoScript['plugin']['tx_viewstatistics']['settings']);
        } else {
            $this->addFlashMessage(
                'Please include the required ViewStatistics Typoscript.',
                'ViewStatistics settings missing',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    /**
     * Create action menu
     * @return void
     */
    protected function createMenu(): void
    {
        $actions = [
            [
                'action' => 'list',
                'label' => LocalizationUtility::translate('tx_viewstatistics_label.module_menu_list', $this->extensionName)
            ],
            [
                'action' => 'listForUser',
                'label' => LocalizationUtility::translate('tx_viewstatistics_label.module_menu_list_for_user', $this->extensionName)
            ],
            [
                'action' => 'listForPage',
                'label' => LocalizationUtility::translate('tx_viewstatistics_label.module_menu_list_for_page', $this->extensionName)
            ],
            [
                'action' => 'listForObject',
                'label' => LocalizationUtility::translate('tx_viewstatistics_label.module_menu_list_for_object', $this->extensionName)
            ],
        ];
        $this->createMenuActions($actions);
    }

    /**
     * Add menu buttons for specific actions
     * @return void
     * @throws Exception
     */
    protected function createButtons(): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        switch ($this->request->getControllerActionName()) {
            case 'list':
                // CSV export
                $this->getButton($buttonBar, 'csv', [
                    'translationKey' => 'csv_export',
                    'action' => 'list',
                    'controller' => 'Backend',
                ]);
                //
                break;
        }
        //
        $this->getButton($buttonBar, 'refresh', [
            'translationKey' => 'list_viewstatistics_refresh'
        ]);
        $this->getButton($buttonBar, 'bookmark', [
            'translationKey' => 'list_viewstatistics_bookmark'
        ]);
    }

    /**
     * View statistics overview
     *
     * @throws NoSuchArgumentException
     * @throws InvalidQueryException
     * @noinspection PhpUnused
     */
    public function listAction(): ResponseInterface
    {
        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['viewstatistics'],
            $this->request,
            ['startDate', 'endDate', 'type', 'feuser']
        );
        // Initialize filtering
        $list = $this->processCustomDateFilter($list);
        $list['type']['items'] = $this->getTypeOptions();
        $list['feuser']['items'] = $this->getFrontendUserOptions();

        if ($this->request->hasArgument('type')) {
            $list['type']['selected'] = trim($this->request->getArgument('type'));
        }
        if ($this->request->hasArgument('feuser')) {
            $list['feuser']['selected'] = trim($this->request->getArgument('feuser'));
        }
        // Store settings
        $this->backendListUtility->writeSettings($list['id'], $list);
        // Export Result as CSV!?
        if ($this->request->hasArgument('csv')) {

            $list['limit'] = 0;
            $list['offset'] = 0;
            $list['pid'] = $this->pageUid;
            $tracking = $this->trackRepository->findAllForBackendList($list);
            $list['countAll'] = $this->trackRepository->findAllForBackendList($list, true);
            $this->backendListUtility->exportAsCsv($tracking, $list);
        } else {

            $list['pid'] = $this->pageUid;
            $tracking = $this->trackRepository->findAllForBackendList($list);
            $list['countAll'] = $this->trackRepository->findAllForBackendList($list, true);
        }
        //
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('tracking', $tracking);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('List');
    }

    /**
     * List tracking data for users
     */
    public function listForUserAction(): ResponseInterface
    {
        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['frontendUser'],
            $this->request,
            ['uid', 'name', 'email']
        );
        // Initialize filtering
        $list['uid'] = '';
        if ($this->request->hasArgument('uid')) {
            $list['uid'] = trim($this->request->getArgument('uid'));
        }
        $list['name'] = '';
        if ($this->request->hasArgument('name')) {
            $list['name'] = trim($this->request->getArgument('name'));
        }
        $list['email'] = '';
        if ($this->request->hasArgument('email')) {
            $list['email'] = trim($this->request->getArgument('email'));
        }
        // Store settings
        $this->backendListUtility->writeSettings($list['id'], $list);
        $frontendUsers = [];
        if (!empty($list['uid'])) {
            $frontendUser = $this->frontendUserRepository->findByUid($list['uid']);
            if ($frontendUser instanceof FrontendUser) {
                $frontendUsers[] = $frontendUser;
            }
        } else {
            $frontendUsers = $this->frontendUserRepository->searchByNameOrEmail($list['name'], $list['email'], false);
            $frontendUsers = $frontendUsers->toArray();
        }
        // Only list fe users who have trackin information
        foreach ($frontendUsers as $key => $feuser) {
            $tracking = $this->trackRepository->findByFrontendUser($feuser);
            if (count($tracking) === 0) {
                unset($frontendUsers[$key]);
            }
        }
        $list['pid'] = $this->pageUid;
        $list['countAll'] = count($frontendUsers);
        $this->moduleTemplate->assign('frontendUsers', $frontendUsers);
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('ListForUser');
    }

    /**
     * List tracking data for a specific user
     *
     * @return ResponseInterface
     * @throws NoSuchArgumentException
     */
    public function userStatisticsAction(): ResponseInterface
    {        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['viewstatistics'],
            $this->request
        );

        if ($this->request->hasArgument('frontendUser')) {
            $userUid = (int)$this->request->getArgument('frontendUser');
        }

        if ($userUid > 0) {
            /** @var ?FrontendUser $frontendUser */
            $frontendUser = $this->frontendUserRepository->findByUid($userUid);
            if ($frontendUser instanceof FrontendUser) {
                // Get tracking data by frontend user
                try {
                    $tracking = $this->trackRepository->findByFrontendUser($frontendUser);
                    $list['countAll'] = $this->trackRepository->findByFrontendUser($frontendUser, true);

                } catch (InvalidQueryException $e) {
                    $tracking = [];
                }
            }
        }
        if (count($tracking) > 0) {
            $this->moduleTemplate->assign('tracking', $tracking);
        }
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('frontendUser', $frontendUser);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('UserStatistics');
    }

    /**
     * List tracking data for a selected page
     */

    /**
     * @return ResponseInterface
     */
    public function listForPageAction(): ResponseInterface
    {
        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['page'],
            $this->request,
            ['pageUid', 'title']
        );
        // Initialize filtering
        if ($this->request->hasArgument('pageUid')) {
            $list['pageUid'] = trim($this->request->getArgument('pageUid'));
        }
        $list['title'] = '';
        if ($this->request->hasArgument('title')) {
            $list['title'] = trim($this->request->getArgument('title'));
        }
        $items = [];
        if (!empty($list['pageUid'])) {
            $items[] = $this->pageRepository->findByUid($list['pageUid']);
            $list['countAll'] = count($items);
        } else {
            $items = $this->pageRepository->searchByTitle($list['title']);
            $list['countAll'] = $this->pageRepository->searchByTitle($list['title'], true);
        }

        $list['type']['selected'] = 'pageview';
        // Store settings
        $this->backendListUtility->writeSettings($list['id'], $list);
        $list['pid'] = $this->pageUid;
        $this->moduleTemplate->assign('items', $items);
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('ListForPage');
    }


    /**
     * @return ResponseInterface
     */
    public function listForSummaryAction(): ResponseInterface
    {
        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['viewsummary'],
            $this->request,
            ['backAction']
        );
        $uid = 0;
        $list['backAction'] = '';
        if ($this->request->hasArgument('uid')) {
            $uid = (int)$this->request->getArgument('uid');
        }
        if ($this->request->hasArgument('type')) {
            $type = $this->request->getArgument('type');
            $this->moduleTemplate->assign('type', $type);
        }
        if ($uid > 0) {
            if ($type === 'pageview') {
                $page = $this->pageRepository->findByUid($uid);
                $this->moduleTemplate->assign('title', $page->getTitle());
                $this->moduleTemplate->assign('label', 'breadcrumb');
                $list['backAction'] = 'listForPage';
                //
                // Page found - read related tracking data
                if ($page) {
                    $tracks = $this->trackRepository->getSummaryByTypeAndUid($type, $uid);
                    $list['countAll'] = $this->trackRepository->getSummaryByTypeAndUid($type, $uid, true);
                }
            } else {
                if (isset($this->settings['types'][$type])) {
                    $settings = $this->settings['types'][$type];
                } else {
                    throw new Exception('Configuration for type ' . $type . ' not found');
                }
                $list['backAction'] = 'listForObject';
                $repositoryClass = $settings['repository'];
                /** @var Repository $repository */
                $repository = GeneralUtility::makeInstance($repositoryClass);
                $object = $repository->findByUid($uid);
                $this->moduleTemplate->assign('uid', $object->getUid());
                $this->moduleTemplate->assign('title', $object->getTitle());
                $this->moduleTemplate->assign('label', $settings['label']);
                // Page found - read related tracking data
                if ($object) {
                    $tracks = $this->trackRepository->getSummaryByTypeAndUid($type, $uid);
                    $list['countAll'] = $this->trackRepository->getSummaryByTypeAndUid($type, $uid, true);
                }
            }
        }
        $list['pid'] = $this->pageUid;

        $this->moduleTemplate->assign('items', $tracks);
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('ListForSummary');
    }


    /**
     * List tracking data for a selected object
     * @throws Exception
     */
    public function listForObjectAction(): ResponseInterface
    {
        // Build list
        $list = $this->backendListUtility->initList(
            $this->settings['lists']['object'],
            $this->request,
            ['type', 'uid', 'title']
        );
        // Initialize filtering
        $list['type']['selected'] = $this->settings['defaultType'];
        if ($this->request->hasArgument('type')) {
            $list['type']['selected'] = trim($this->request->getArgument('type'));
        }
        $list['type']['items'] = $this->getTypeOptions(true);
        if ($this->request->hasArgument('uid')) {
            $list['uid'] = trim($this->request->getArgument('uid'));
        }
        $list['title'] = '';
        if ($this->request->hasArgument('title')) {
            $list['title'] = trim($this->request->getArgument('title'));
        }
        // Get object settings from typoscript

        if (isset($this->settings['types'][$list['type']['selected']])) {
            $settings = $this->settings['types'][$list['type']['selected']];
        } else {
            $type = $list['type']['selected'] ?? '';
            $message = $type ? "Configuration for type $type not found. " : "Configuration not found. ";
            $message .= "Please make sure to include the required ViewStatistics Typoscript.";
            throw new Exception($message);
        }
        // Store settings
        $this->backendListUtility->writeSettings($list['id'], $list);
        $list['field'] = $settings['field'];

        $tracking = $this->trackRepository->findAllForObjectsBackendList($list);
        $list['countAll'] = $this->trackRepository->findAllForObjectsBackendList($list, true);

        $list['pid'] = $this->pageUid;
        $this->moduleTemplate->assign('tracking', $tracking);
        $this->moduleTemplate->assign('list', $list);
        $this->moduleTemplate->assign('currentPage', $this->pageUid);
        $this->moduleTemplate->assign('actionMethodName', $this->actionMethodName);
        return $this->moduleTemplate->renderResponse('ListForObject');
    }

    /**
     * @param array<string, mixed> $list
     * @return array<string, mixed>
     * @throws NoSuchArgumentException
     */
    protected function processCustomDateFilter(array $list): array
    {
        // Initialise start and end dates of search
        [$startDate, $endDate] = [
            (new DateTime())->setTimestamp(time() - (60 * 60 * 24)),
            (new DateTime())->setTimestamp(time()),
        ];

        if ($this->request->hasArgument('startDate')) {
            $list['startDate'] = $this->request->getArgument('startDate');
            if (strlen((string)$list['startDate']) > 0) {
                $startDate = new DateTime((string)$list['startDate']);
            }
        }
        if ($this->request->hasArgument('endDate')) {
            $list['endDate'] = $this->request->getArgument('endDate');
            if (strlen((string)$list['endDate']) > 0) {
                $endDate = new DateTime((string)$list['endDate']);
            }
        }
        if ($endDate < $startDate) {
            $this->addFlashMessage(
                'Datepicker to date must be later than from date',
                'Warning',
                ContextualFeedbackSeverity::WARNING
            );
        }
        $list['period']['start'] = ['timestamp' => $startDate->getTimeStamp(), 'datetime' => $startDate];
        $list['period']['end'] = ['timestamp' => $endDate->getTimeStamp(), 'datetime' => $endDate];
        return $list;
    }

    protected function getTypeOptions($withoutDefault = false)
    {
        // Default types
        $types = [];
        if (!$withoutDefault) {
            $types = [
                'pageview' => $this->translate('tx_viewstatistics_label.track_type_pageview'),
                'login' => $this->translate('tx_viewstatistics_label.track_type_login')
            ];
        }
        // Types from configuration
        if (isset($this->settings['types']) && count($this->settings['types']) > 0) {
            foreach ($this->settings['types'] as $typeKey => $typeSettings) {
                if (ExtensionManagementUtility::isLoaded($typeSettings['extensionKey'])) {
                    $types[$typeKey] = $typeSettings['label'];
                }
            }
        }
        return $types;
    }

    /**
     * @return array
     */
    protected function getFrontendUserOptions()
    {
        return [
            'anonym' => $this->translate('tx_viewstatistics_label.frontend_user_type_anonym'),
            'logged_in' => $this->translate('tx_viewstatistics_label.frontend_user_type_logged_in')
        ];
    }
}
