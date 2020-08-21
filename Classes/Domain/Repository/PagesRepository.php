<?php

namespace RKW\RkwRelated\Domain\Repository;

use RKW\RkwBasics\Helper\QueryTypo3;

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

/**
 * PagesRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PagesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{


    public function initializeObject()
    {
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $querySettings->setRespectStoragePage(false);
    }

    /**
     * Get pages with equal projects - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \RKW\RkwProjects\Domain\Model\Projects $project
     * @param array $excludePidList
     * @param array $includePidList
     * @param integer $pageNumber
     * @param integer $limit
     * @param bool $ignoreVisibility
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByProject($project, $excludePidList, $includePidList, $pageNumber = 1, $limit = 5, $ignoreVisibility = false)
    {
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
                    'txRkwpdf2contentIsImport' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                    'lastUpdated'             => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );

        } else {
            $query->setOrderings(
                array(
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );
        }

        if ($pageNumber <= 1) {
            $query->setOffset(0);
        } else {
            $query->setOffset((intval($pageNumber) - 1) * $limit);
        }
        $query->setLimit($limit + 1);

        return $query->execute();
    }


    /**
     * Get pages with equal projects - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \RKW\RkwBasics\Domain\Model\Department $department
     * @param array $excludePidList
     * @param array $includePidList
     * @param integer $pageNumber
     * @param integer $limit
     * @param bool $ignoreVisibility
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByDepartment($department, $excludePidList, $includePidList, $pageNumber = 1, $limit = 5, $ignoreVisibility = false)
    {
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
                    'txRkwpdf2contentIsImport' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                    'lastUpdated'             => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );

        } else {
            $query->setOrderings(
                array(
                    'lastUpdated' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING,
                )
            );
        }


        if ($pageNumber <= 1) {
            $query->setOffset(0);
        } else {
            $query->setOffset((intval($pageNumber) - 1) * $limit);
        }

        $query->setLimit($limit + 1);

        return $query->execute();
    }


    /**
     * Get pages with equal categories - except the current pid
     * Sorting: Last created / edited pages first!
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategories
     * @param array $excludePidList
     * @param integer parentCategory
     * @param integer $pageNumber
     * @param integer $limit
     * @param bool $ignoreVisibility
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBySysCategory($sysCategories, $excludePidList, $parentCategory = 0, $pageNumber = 1, $limit = 5, $ignoreVisibility = false)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
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

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {
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
                '(((tx_rkwprojects_domain_model_projects.sys_language_uid IN (0,-1))) OR tx_rkwprojects_domain_model_projects.uid IS NULL)',
                'NOT(pages.uid IN (' . implode(',', $excludePidList) . '))',
                'pages.doktype IN (\'1\')',
                'pages.no_search = 0',
            );

            if (! $ignoreVisibility) {
                $constraints[] = '((SELECT visibility FROM tx_rkwbasics_domain_model_documenttype WHERE uid = pages.tx_rkwbasics_document_type) = 1)';
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


            // 5. Offset
            $offset = ((intval($pageNumber) - 1) * $limit) + 1;
            if ($pageNumber <= 1) {
                $offset = 0;
            }

            // 6. Final statement
            $query->statement('
                SELECT count(pages.uid) as counter, pages.uid, ' . implode(', pages.', $select) . ' FROM pages 
                ' . $leftJoin . '
                WHERE 
                    sys_category.uid IN(' . implode(',', $sysCategoriesList) . ')
                    AND ' . implode(' AND ', $constraints) .
                QueryTypo3::getWhereClauseForEnableFields('pages') .
                QueryTypo3::getWhereClauseForDeleteFields('pages') .
                '
                GROUP BY pages.uid
                ORDER BY counter ' . \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
                . ', ' . implode(',', $order) . '
                LIMIT ' . ($limit + 1) . '
                OFFSET ' . $offset . '
            ');

            return $query->execute();
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
     * @param integer $pageNumber
     * @param integer $limit
     * @param bool $ignoreVisibility
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByConfiguration($excludePidList, $includePidList, $filterList, $findPublications = 0, $pageNumber = 0, $limit = 10, $ignoreVisibility = false)
    {
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
                $query->greaterThanOrEqual('crdate', $dateFrom),
                $query->lessThanOrEqual('crdate', $dateUntil)
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

        if ($pageNumber) {
            if ($pageNumber <= 1) {
                $query->setOffset(0);
            } else {
                $query->setOffset((intval($pageNumber) - 1) * intval($limit));
            }
            $query->setLimit(intval($limit));
        }

        return $query->execute();
    }



}