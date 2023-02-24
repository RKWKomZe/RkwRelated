<?php
namespace RKW\RkwRelated\Domain\Repository;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Madj2k\CoreExtended\Utility\QueryUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * PagesRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @return void     */

    public function initializeObject(): void
    {
        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
    }


    /**
     * Get pages with equal projects - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \RKW\RkwProjects\Domain\Model\Projects $project
     * @param array $excludePidList
     * @param array $includePidList
     * @param int $pageNumber
     * @param int $limit
     * @param bool $ignoreVisibility
     * @param bool $randomResult
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByProject(

        \RKW\RkwProjects\Domain\Model\Projects $project,
        array $excludePidList,
        array $includePidList,
        int $pageNumber = 1,
        int $limit = 5,
        bool $ignoreVisibility = false,
        bool $randomResult = false
    ): QueryResultInterface {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = array(
            $query->logicalOr(
                $query->equals('txRkwprojectsProjectUid', $project)
            ),
            $query->logicalNot(
                $query->in('uid', $excludePidList)
            ),
        );

        // search only in a given pid list (PID's of a rootline)
        if (
            ($includePidList)
            && ($includePidList[0])
        ) {
            $constraints[] = $query->in('uid', $includePidList);
        }

        // search only real pages
        $constraints[] = $query->in('doktype', array('1'));

        // do not include pages which are excluded from search or visibility
        $constraints[] = $query->equals('noSearch', 0);
        if (! $ignoreVisibility) {
            $constraints[] = $query->equals('txRkwbasicsDocumentType.visibility', 1);
        }

        // exclude txBmpdf2contentIsImportSub
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')) {
            $constraints[] =
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->equals('txRkwpdf2contentIsImport', 1),
                        $query->equals('txRkwpdf2contentIsImportSub', 0)
                    ),
                    $query->equals('txRkwpdf2contentIsImport', 0)
                );
        }

        $query->matching($query->logicalAnd($constraints));


        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')) {
            $query->setOrderings(
                array(
                    // 'txRkwpdf2contentIsImport' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );

        } else {
            $query->setOrderings(
                array(
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );
        }

        // random sorting overrides offset, so pagination is not possible
        if($randomResult) {
            $query->setOffset($this->getRandomOffset($query, $limit));
        } else {
            $query->setOffset($this->getDefaultOffset($limit, $pageNumber));
        }

        $query->setLimit($limit);
        return $query->execute();
    }


    /**
     * Get pages with equal projects - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \RKW\RkwBasics\Domain\Model\Department $department
     * @param array $excludePidList
     * @param array $includePidList
     * @param int $pageNumber
     * @param int $limit
     * @param bool $ignoreVisibility
     * @param bool $randomResult
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByDepartment(
        \RKW\RkwBasics\Domain\Model\Department $department,
        array $excludePidList,
        array $includePidList,
        int $pageNumber = 1,
        int $limit = 5,
        bool $ignoreVisibility = false,
        bool $randomResult = false
    ): QueryResultInterface {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = array(
            $query->logicalOr(
                $query->equals('txRkwbasicsDepartment', $department)
            ),
            $query->logicalNot(
                $query->in('uid', $excludePidList)
            ),
        );

        // search only in a given pid list (PID's of a rootline)
        if (
            ($includePidList)
            && ($includePidList[0])
        ) {
            $constraints[] = $query->in('uid', $includePidList);
        }

        // search only real pages
        $constraints[] = $query->in('doktype', array('1'));

        // do not include pages which are excluded from search or visibility
        $constraints[] = $query->equals('noSearch', 0);
        if (! $ignoreVisibility) {
            $constraints[] = $query->equals('txRkwbasicsDocumentType.visibility', 1);
        }

        // exclude txBmpdf2contentIsImportSub
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')) {
            $constraints[] =
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->equals('txRkwpdf2contentIsImport', 1),
                        $query->equals('txRkwpdf2contentIsImportSub', 0)
                    ),
                    $query->equals('txRkwpdf2contentIsImport', 0)
                );
        }

        $query->matching($query->logicalAnd($constraints));


        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')) {
            $query->setOrderings(
                array(
                 //   'txRkwpdf2contentIsImport' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );

        } else {
            $query->setOrderings(
                array(
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );
        }

        // random sorting overrides offset, so pagination is not possible
        if($randomResult) {
            $query->setOffset($this->getRandomOffset($query, $limit));
        } else {
            $query->setOffset($this->getDefaultOffset($limit, $pageNumber));
        }

        $query->setLimit($limit);
        return $query->execute();
    }


    /**
     * Get pages with equal categories - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategories
     * @param array $excludePidList
     * @param int parentCategory
     * @param int $pageNumber
     * @param int $limit
     * @param bool $ignoreVisibility
     * @param bool $randomResult
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|null
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function findBySysCategory(
        \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategories,
        array $excludePidList,
        int $parentCategory = 0,
        int $pageNumber = 1,
        int $limit = 5,
        bool $ignoreVisibility = false,
        bool $randomResult = false
    ):? QueryResultInterface {

        $result = null;

        // build query for matching pages
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $queryPages */
        $queryPages = $this->buildQueryFindBySysCategory(
            $sysCategories,
            $excludePidList,
            $parentCategory,
            $pageNumber,
            $limit,
            $ignoreVisibility,
            $randomResult,
            0
        );

        // build second best version: query with matching projects
        /** @var \TYPO3\CMS\Extbase\Persistence\QueryInterface $queryProjects */
        $queryProjects = $this->buildQueryFindBySysCategory(
            $sysCategories,
            $excludePidList,
            $parentCategory,
            $pageNumber,
            $limit,
            $ignoreVisibility,
            $randomResult,
            1
        );

        if ($queryPages) {

            // check for matching pages - if nothing is found, take project-matches instead!
            $result = $queryPages->execute();
            if (
                (! $result)
                && ($queryProjects)
            ) {
                $result = $queryProjects->execute();
            }
        }

        return $result;
    }


    /**
     * Get pages with equal categories - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategories
     * @param array $excludePidList
     * @param int parentCategory
     * @param int $pageNumber
     * @param int $limit
     * @param bool $ignoreVisibility
     * @param bool $randomResult
     * @param int $type
     * @return null|\TYPO3\CMS\Extbase\Persistence\QueryInterface
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    protected function buildQueryFindBySysCategory(
        \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategories,
        array $excludePidList,
        int $parentCategory = 0,
        int $pageNumber = 1,
        int $limit = 5,
        bool $ignoreVisibility = false,
        bool $randomResult = false,
        int $type = 0
    ) {

        $select = array_keys($GLOBALS['TCA']['pages']['columns']);
        $order = array();

        // 1. build uid list
        $sysCategoriesList = array();

        /** @var \TYPO3\CMS\Extbase\Domain\Model\Category $category */
        foreach ($sysCategories as $category) {
            if ($category instanceof \TYPO3\CMS\Extbase\Domain\Model\Category) {
                $sysCategoriesList[] = $category->getUid();
            }
        }

        if (count($sysCategoriesList)) {

            // 2. set leftJoin over categories
            $leftJoin = '
                LEFT JOIN sys_category_record_mm AS sys_category_record_mm
                    ON pages.uid=sys_category_record_mm.uid_foreign
                    AND sys_category_record_mm.tablenames = \'pages\'
                    AND sys_category_record_mm.fieldname = \'categories\'
                LEFT JOIN sys_category AS sys_category
                    ON sys_category_record_mm.uid_local=sys_category.uid
                    AND sys_category.deleted = 0
            ';

            if (
                (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects'))
                && ($type == 1)
            ){
                $leftJoin = '
                    LEFT JOIN tx_rkwprojects_domain_model_projects AS tx_rkwprojects_domain_model_projects
                        ON pages.tx_rkwprojects_project_uid=tx_rkwprojects_domain_model_projects.uid
                    LEFT JOIN sys_category_record_mm AS sys_category_record_mm
                        ON tx_rkwprojects_domain_model_projects.uid=sys_category_record_mm.uid_foreign
                        AND sys_category_record_mm.tablenames = \'tx_rkwprojects_domain_model_projects\'
                        AND sys_category_record_mm.fieldname = \'sys_category\'
                    LEFT JOIN sys_category AS sys_category
                        ON sys_category_record_mm.uid_local=sys_category.uid
                        AND sys_category.deleted = 0
                ';
            }

            if ($parentCategory) {
                $leftJoin .= ' AND sys_category.parent = ' . intval($parentCategory);
            }

            // 3. set constraints
            $constraints = array(
                '(((sys_category.sys_language_uid IN (0,-1))) OR sys_category.uid IS NULL)',
                'NOT(pages.uid IN (' . implode(',', $excludePidList) . '))',
                'pages.doktype IN (\'1\')',
                'pages.no_search = 0',
            );

            if (! $ignoreVisibility) {
                $constraints[] = '((SELECT visibility FROM tx_rkwbasics_domain_model_documenttype WHERE uid = pages.tx_rkwbasics_document_type) = 1)';
            }

            if (
                (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects'))
                && ($type == 1)
            ){
                $constraints[] = '(((tx_rkwprojects_domain_model_projects.sys_language_uid IN (0,-1))) OR tx_rkwprojects_domain_model_projects.uid IS NULL)';
            }

            // exclude txBmpdf2contentIsImportSub
            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')) {
                $constraints[] = '(
                    (
                        pages.tx_rkwpdf2content_is_import = 1
                        AND pages.tx_rkwpdf2content_is_import_sub = 0
                    )
                    OR pages.tx_rkwpdf2content_is_import = 0
                )';

                // $order[] = 'pages.tx_rkwpdf2content_is_import ' . \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
            }

            // only pages that are older than this
            // $constraints[] = 'pages.lastUpdated < ' . intval($page->getLastUpdated());

            // 4. set second ordering
            $order[] = 'lastUpdated ' . \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;

            // 5. Final statement
            $finalStatement = '
                SELECT count(pages.uid) as counter, pages.uid, ' . implode(', pages.', $select) . ' FROM pages
                ' . $leftJoin . '
                WHERE
                    sys_category.uid IN(' . implode(',', $sysCategoriesList) . ')
                    AND ' . implode(' AND ', $constraints) .
                QueryUtility::getWhereClauseEnabled('pages') .
                QueryUtility::getWhereClauseDeleted('pages') .
                '
                GROUP BY pages.uid
                ORDER BY counter ' . \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
                . ', ' . implode(',', $order);


            // random sorting overrides offset, so pagination is not possible
            if($randomResult) {
                $query = $this->createQuery();
                $query->getQuerySettings()->setRespectStoragePage(false);
                $query->statement($finalStatement);
                $offset = $this->getRandomOffset($query, $limit);

            } else {
                $offset = $this->getDefaultOffset($limit, $pageNumber);
            }

            // build final query
            $query = $this->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            $query->statement(
                $finalStatement . '
                LIMIT ' . ($limit) . '
                OFFSET ' . $offset
            );

            return $query;
        }

        return null;
    }


    /**
     * Get pages with criteria from flexform / tt_content plugin element filter options
     * Sorting: Last created / edited pages first!
     *
     * @param array $excludePidList
     * @param array $includePidList
     * @param array $filterList
     * @param int $findPublications
     * @param int $pageNumber
     * @param int $limit
     * @param bool $ignoreVisibility
     * @param bool $randomResult
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByConfiguration(
        array $excludePidList,
        array $includePidList,
        array $filterList,
        int $findPublications = 0,
        int $pageNumber = 0,
        int $limit = 10,
        bool $ignoreVisibility = false,
        bool $randomResult = false
    ) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = array();

        if ($filterList['department']) {
            $constraints[] = $query->in('txRkwbasicsDepartment', $filterList['department']);
        }
        if (isset($filterList['documentType'])) {
            if ($filterList['documentType']) {
                $constraints[] = $query->in('txRkwbasicsDocumentType', $filterList['documentType']);
            }
        }
        if ($filterList['year']) {
            $dateFrom = strtotime(intval($filterList['year']) . '-01-01');
            $dateUntil = strtotime(intval($filterList['year']) . '-12-31');
            $constraints[] = $query->logicalAnd(
                $query->greaterThanOrEqual('lastUpdated', $dateFrom),
                $query->lessThanOrEqual('lastUpdated', $dateUntil)
            );
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {
            if (isset($filterList['project'])) {
                if ($filterList['project']) {
                    $constraints[] = $query->in('txRkwprojectsProjectUid', $filterList['project']);
                }
            }
        }

        $constraints[] = $query->logicalNot($query->in('uid', $excludePidList));

        // search only in a given pid list (PID's of a rootline)
        if (
            ($includePidList)
            && ($includePidList[0])
        ) {
            $constraints[] = $query->in('uid', $includePidList);
        }

        // search only real pages
        $constraints[] = $query->in('doktype', array('1'));

        // do not include pages which are excluded from search
        $constraints[] = $query->equals('noSearch', 0);

        if (! $ignoreVisibility) {
            $constraints[] = $query->equals('txRkwbasicsDocumentType.visibility', 1);
        }

        // exclude or include publication via txRkwpdf2contentIsImportSub
        if (
            (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content'))
            && ($findPublications > 0)
        ){
            if ($findPublications == 1) {
                $constraints[] = $query->logicalAnd(
                    $query->equals('txRkwpdf2contentIsImport', 1),
                    $query->equals('txRkwpdf2contentIsImportSub', 0)
                );

            } else {
                $constraints[] = $query->equals('txRkwpdf2contentIsImport', 0);
            }

        } else {
            $constraints[] =
                $query->logicalOr(
                    $query->logicalAnd(
                        $query->equals('txRkwpdf2contentIsImport', 1),
                        $query->equals('txRkwpdf2contentIsImportSub', 0)
                    ),
                    $query->equals('txRkwpdf2contentIsImport', 0)
                );
        }

        // NOW: construct final query!
        $query->matching($query->logicalAnd($constraints));
        $query->setOrderings(
            array(
                'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
            )
        );

        // random sorting overrides offset, so pagination is not possible
        if($randomResult) {
            $query->setOffset($this->getRandomOffset($query, $limit));
        } else {
            $query->setOffset($this->getDefaultOffset($limit, $pageNumber));
        }

        $query->setLimit(intval($limit));
        return $query->execute();
    }



    /**
     * Get random offset
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @param int $limit
     * @return int
     */
    protected function getRandomOffset(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query, $limit)
    {
        $rowCount = $query->execute()->count();
        $offset = mt_rand(0, max(0, ($rowCount - $limit - 1)));
        return $offset;
    }


    /**
     * Get default offset
     *
     * @param int $limit
     * @param int $pageNumber
     * @return int
     */
    protected function getDefaultOffset($limit, $pageNumber)
    {
        $offset = ((intval($pageNumber) - 1) * $limit);
        if ($pageNumber <= 1) {
            $offset = 0;
        }
        return $offset;
    }

}
