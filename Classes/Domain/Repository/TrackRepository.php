<?php

namespace CodingMs\ViewStatistics\Domain\Repository;

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

use CodingMs\Modules\Domain\Repository\Traits\Typo3Version13Trait;
use CodingMs\ViewStatistics\Domain\Model\PageViewSummary;
use CodingMs\ViewStatistics\Domain\Model\TrackObject;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

/**
 * The repository for Track
 */
class TrackRepository extends Repository
{
    use Typo3Version13Trait;

    /**
     * Admin is using the module
     * @var bool
     */
    protected $isAdmin = false;

    /**
     * Array with accessible pages for editor
     * @var array
     */
    protected $accessiblePages = [];

    /**
     * @param $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    /**
     * @param array $accessiblePages
     */
    public function setAccessiblePages(array $accessiblePages = [])
    {
        $this->accessiblePages = $accessiblePages;
    }

    public function initializeObject()
    {
        /** @var $querySettings Typo3QuerySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Get all tracking data.
     * Respects editor authorizations.
     *
     * @param string $sortingField
     * @param string $sortingOrder
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAll($sortingField = 'crdate', $sortingOrder = QueryInterface::ORDER_DESCENDING, $dateFrom = null, $dateTo = null)
    {
        $query = $this->createQuery();
        $orderings = [
            $sortingField => $sortingOrder,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $query->matching($query->in('page', $this->accessiblePages));
        }
        $query->setOrderings($orderings);
        return $query->execute();
    }

    /**
     * Counts all tracking data by type and page uid.
     * Respects editor authorizations.
     *
     * @param string $type What kind of tracking data should be found
     * @param int $pageUid
     * @return int
     * @throws InvalidQueryException
     */
    public function countByTypeAndUid($type, $pageUid)
    {
        $query = $this->createQuery();
        $orderings = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        // Page uid
        // if($pageUid > 0) {
        //     $whereParts[] = $query->equals('page', $pageUid);
        // }
        // Type
        switch ($type) {
            case 'pageview':
                $whereParts[] = $query->equals('action', 'pageview');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            case 'login':
                $whereParts[] = $query->equals('action', 'login');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            default:
                // Filter by object type
                $whereParts[] = $query->equals('object_type', $type);
                $whereParts[] = $query->equals('object_uid', $pageUid);
                break;
        }
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd(...$whereParts));
        }
        $query->setOrderings($orderings);
        return $query->execute()->count();
    }

    /**
     * Get all tracking data by type and page uid.
     * Respects editor authorizations.
     *
     * @param string $type What kind of tracking data should be found
     * @param int $pageUid
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByTypeAndUid($type, $pageUid)
    {
        $query = $this->createQuery();
        $orderings = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        // Page uid
        //if($pageUid > 0) {
        // $whereParts[] = $query->equals('page', $pageUid);
        //}
        // Type
        switch ($type) {
            case 'pageview':
                $whereParts[] = $query->equals('action', 'pageview');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            case 'login':
                $whereParts[] = $query->equals('action', 'login');
                if ($pageUid > 0) {
                    $whereParts[] = $query->equals('page', $pageUid);
                }
                break;
            default:
                // Filter by object type
                $whereParts[] = $query->equals('object_type', $type);
                $whereParts[] = $query->equals('object_uid', $pageUid);
                break;
        }
        // Non admin must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd(...$whereParts));
        }
        $query->setOrderings($orderings);
        $query->setLimit(50000);
        $result = $query->execute(true);
        return $result;
    }

    /**
     * Get all tracking data by type and page uid.
     * Respects editor authorizations.
     *
     * @param string $type What kind of tracking data should be found
     * @param int $pageUid
     * @return array|QueryResultInterface|int
     * @throws InvalidQueryException
     */
    public function getSummaryByTypeAndUid($type = '', $uid = 0, $count = false)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');

        if (!empty($type)) {
            if ($type === 'pageview') {
                $constraints[] = $queryBuilder->expr()->eq(
                    'page',
                    $queryBuilder->createNamedParameter($uid, self::getPdoIntParam())
                );
                $constraints[] = $queryBuilder->expr()->like(
                    'action',
                    $queryBuilder->createNamedParameter('%' . $type . '%')
                );
            } else {
                $constraints[] = $queryBuilder->expr()->eq(
                    'object_uid',
                    $queryBuilder->createNamedParameter($uid, self::getPdoIntParam())
                );
                $constraints[] = $queryBuilder->expr()->eq(
                    'object_type',
                    $queryBuilder->createNamedParameter($type)
                );
            }
        }
        if (!empty($constraints)) {
            $queryBuilder->where(
                ...$constraints
            );
        }
        /** @var Result $result */
        $result = $queryBuilder
            //->select('account','number', 'inventory', 'make', 'model')
            ->addSelectLiteral('DATE(FROM_UNIXTIME(crdate)) as ' . $queryBuilder->quoteIdentifier('creationDate') .
                ', COUNT(*) AS ' . $queryBuilder->quoteIdentifier('total') .
                ', COUNT(CASE WHEN frontend_user > 0 THEN 1 END) AS ' . $queryBuilder->quoteIdentifier('frontendUserTotal'))
            ->from('tx_viewstatistics_domain_model_track')
            ->groupBy('creationDate')
            ->executeQuery();
        if ($result instanceof Result) {
            $resultArray = $result->fetchAllAssociative();
            if ($count) {
                return count($resultArray);
            } else {
                $objects = [];
                foreach ($resultArray as $row) {
                    $rowObject = $this->getPropertyMapper()->convert($row, PageViewSummary::class);
                    $objects[] = $rowObject;
                }
                return $objects;
            }
        }
    }

    /**
     *
     * Get all tracking data by frontend user
     *
     * @param $frontendUser
     * @param bool $count
     * @return int|\mixed[][]|QueryResultInterface
     */
    public function findByFrontendUser($frontendUser, bool $count = false)
    {
        $query = $this->createQuery();
        $ordering = [
            'crdate' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        //
        $whereParts = [];
        $whereParts[] = $query->equals('frontend_user', $frontendUser->getUid());
        //
        if (count($whereParts) === 1) {
            $query->matching($whereParts[0]);
        } else {
            $query->matching($query->logicalAnd(...$whereParts));
        }
        if (!$count) {
            $query->setOrderings($ordering);
            $result = $query->execute(false);
            return $result;
        }
        return $query->execute(false)->count();
    }

    /**
     * Get all tracking data for main overview.
     * Respects editor authorizations.
     *
     * @param array $filter
     * @param string $sortingField
     * @param QueryInterface $sortingOrder
     * @return array|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllFiltered($filter, $sortingField = 'crdate', $sortingOrder = QueryInterface::ORDER_DESCENDING)
    {
        $query = $this->createQuery();
        $whereParts = [];
        // Date from
        if (isset($filter['mindate_ts'])) {
            $whereParts[] = $query->greaterThanOrEqual('crdate', $filter['mindate_ts']);
        }
        // Date to
        if (isset($filter['maxdate_ts'])) {
            $whereParts[] = $query->lessThanOrEqual('crdate', $filter['maxdate_ts']);
        }
        // Type
        if (isset($filter['type'])) {
            switch ($filter['type']) {
                case 'pageview':
                    $whereParts[] = $query->equals('action', 'pageview');
                    break;
                case 'login':
                    $whereParts[] = $query->equals('action', 'login');
                    break;
                default:
                    // Filter by object type
                    $whereParts[] = $query->equals('object_type', $filter['type']);
                    break;
            }
        }
        // Frontend user
        if (isset($filter['frontendUser'])) {
            switch ($filter['frontendUser']) {
                case 'anonym':
                    $whereParts[] = $query->equals('frontend_user', 0);
                    break;
                case 'logged_in':
                    $whereParts[] = $query->greaterThan('frontend_user', 0);
                    break;
            }
        }
        // User is admin?
        // Non admins are not permitted to read all pages, they must have access to related page!
        if (!$this->isAdmin) {
            $whereParts[] = $query->equals('page', $filter['pageUid']);
            $whereParts[] = $query->in('page', $this->accessiblePages);
        }
        // Are there some where-parts?
        if (count($whereParts) > 0) {
            // ..we need a logical AND for matching
            $where = $query->logicalAnd(...$whereParts);
            $query->matching($where);
        }
        $orderings = [
            $sortingField => $sortingOrder,
            'uid' => QueryInterface::ORDER_DESCENDING
        ];
        $query->setOrderings($orderings);
        $result = $query->execute();
        return $result;
    }

    /**
     * @param mixed[] $filter
     * @param bool $count
     * @return array|QueryResultInterface|int
     * @throws InvalidQueryException
     */
    public function findAllForBackendList(array $filter = [], $count = false)
    {
        $query = $this->createQuery();
        //
        // Filters
        $constraints = [];
        if (isset($filter['feuser']['selected']) && !empty($filter['feuser']['selected'])) {
            if ($filter['feuser']['selected'] === 'logged_in') {
                $constraints[] = $query->greaterThan('frontend_user', 0);
            } elseif ($filter['feuser']['selected'] === 'anonym') {
                $constraints[] = $query->equals('frontend_user', 0);
            }
        }
        // Type
        if (!empty($filter['type']['selected'])) {
            switch ($filter['type']['selected']) {
                case 'pageview':
                    $constraints[] = $query->equals('action', 'pageview');
                    break;
                case 'login':
                    $constraints[] = $query->equals('action', 'login');
                    break;
                default:
                    // Filter by object type
                    $constraints[] = $query->equals('object_type', $filter['type']['selected']);
                    break;
            }
        }
        if (isset($filter['period']['start']['timestamp'])) {
            $constraints[] = $query->greaterThanOrEqual('crdate', $filter['period']['start']['timestamp']);
        }
        if (isset($filter['period']['end']['timestamp'])) {
            $constraints[] = $query->lessThanOrEqual('crdate', $filter['period']['end']['timestamp']);
        }
        if (count($constraints) > 0) {
            $query->matching(
                $query->logicalAnd(...$constraints)
            );
        }
        if (!$count) {
            if (isset($filter['sortingField']) && $filter['sortingField'] !== '') {
                if ($filter['sortingOrder'] == 'asc') {
                    $query->setOrderings([$filter['sortingField'] => QueryInterface::ORDER_ASCENDING]);
                } elseif ($filter['sortingOrder'] == 'desc') {
                    $query->setOrderings([$filter['sortingField'] => QueryInterface::ORDER_DESCENDING]);
                }
            }
            if ((int)$filter['limit'] > 0) {
                $query->setOffset((int)$filter['offset']);
                $query->setLimit((int)$filter['limit']);
            }
            return $query->execute();
        }
        return $query->execute()->count();
    }

    /**
     * @param mixed[] $filter
     * @param bool $count
     * @return array|QueryResultInterface|int
     * @throws InvalidQueryException
     */
    public function findAllForObjectsBackendList(array $filter = [], $count = false)
    {
        // Get objects by relation
        // So we get only records that have been already tracked!
        $objects = [];
        // Fix table name for sys_files
        if ($filter['type']['selected'] === 'sys_file') {
            $filter['type']['selected'] = 'sys_file_metadata';
        }
        // Set title of object
        if ($filter['field']) {
            $field = 'type.' . $filter['field'];
        } else {
            $field = 'type.title';
        }
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_viewstatistics_domain_model_track');
        $queryBuilder->select('type.uid', $field, 'track.object_type', 'track.crdate')
            ->from('tx_viewstatistics_domain_model_track', 'track')
            ->join('track',
                $filter['type']['selected'],
                'type',
                'track.object_uid = type.uid'
            );;
        $queryBuilder->where(
            $queryBuilder->expr()->gt('object_uid', '0')
        );
        if (!empty($filter['title'])) {
            $queryBuilder->andWhere($queryBuilder->expr()->like($field,
                $queryBuilder->createNamedParameter('%' . $filter['title'] . '%')
            ));
        }
        if (!empty($filter['uid'])) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('track.object_uid',
                $queryBuilder->createNamedParameter($filter['uid'], self::getPdoIntParam())
            ));
        }
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'object_type',
                $queryBuilder->createNamedParameter($filter['type']['selected'])
            )
        );
        if (!$count) {
            if (isset($filter['sortingField']) && $filter['sortingField'] != '') {
                $sortingField = $filter['sortingField'];
                if (isset($filter['fields'][$filter['sortingField']]['sortingField'])) {
                    $sortingField = $filter['fields'][$filter['sortingField']]['sortingField'];
                }
                if ($filter['sortingOrder'] == 'asc') {
                    $queryBuilder->orderBy($sortingField, 'ASC');
                } else {
                    $queryBuilder->orderBy($sortingField, 'DESC');
                }
            }
        }
        $queryBuilder->setMaxResults(5000);
        $queryBuilder->orderBy('track.crdate', 'DESC');
        $queryBuilder->groupBy('track.object_uid');
        $result = $queryBuilder->executeQuery();
        if ($result instanceof Result) {
            $resultArray = $result->fetchAllAssociative();
            $objects = [];
            foreach ($resultArray as $row) {
                $row['type'] = $row['object_type'];
                unset($row['object_type']);
                $rowObject = $this->getPropertyMapper()->convert($row, TrackObject::class);
                $objects[] = $rowObject;
            }

        }
        if (!$count) {
            return $objects;
        }
        return count($objects);
    }

    /**
     * @return PropertyMapper
     */
    protected function getPropertyMapper(): PropertyMapper
    {
        /** @var PropertyMapper $propertyMapper */
        $propertyMapper = GeneralUtility::makeInstance(PropertyMapper::class);
        return $propertyMapper;
    }

}
