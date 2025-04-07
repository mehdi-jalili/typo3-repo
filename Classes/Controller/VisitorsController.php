<?php

namespace CodingMs\ViewStatistics\Controller;

/***************************************************************
 *
 * Copyright notice
 *
 
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

use Doctrine\DBAL\Result;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class VisitorsController
 */
class VisitorsController extends ActionController
{
    /**
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function showAction(): ResponseInterface
    {
        $visitors = 0;
        /** @var SiteFinder $siteFinder */
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId((int)$GLOBALS['TSFE']->id);
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var Connection $connection */
        $connection = $connectionPool->getConnectionForTable('pages');
        $queryBuilder = $connection->createQueryBuilder();
        $result = $queryBuilder->select('visitors')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $site->getRootPageId()))
            ->setMaxResults(1)
            ->executeQuery();
        if ($result instanceof Result) {
            $visitors = $result->fetchAssociative()['visitors'];
        }
        $this->view->assign('visitors', $visitors);
        return $this->htmlResponse();
    }
}
