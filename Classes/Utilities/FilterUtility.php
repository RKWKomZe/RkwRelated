<?php

namespace RKW\RkwRelated\Utilities;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use RKW\RkwRelated\Domain\Repository\PagesRepository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
* Filter
*
* @author Steffen Kroggel <developer@steffenkroggel.de>
* @copyright Rkw Kompetenzzentrum
* @package RKW_Related
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
*/

class FilterUtility
{

    /**
     * @const
     */
    const VALID_FILTERS = [
        'documentType' => 'getTxRkwBasicsDocumentType',
        'department' => 'getTxRkwbasicsDepartment',
        'project' => 'getTxRkwprojectsProjectUid',
        'categories' => 'getSysCategory'
    ];


    /**
     * Gets list pids to exclude
     *
     * @param array $settings
     * @return array
     */
    public static function getExcludePidList(array $settings): array
    {

        // if a special startingPid is set, set it as rootPid
        $excludePages = [intval($GLOBALS['TSFE']->id)];
        if ($settings['startingPid']) {
            $excludePages[] = intval($settings['startingPid']);
        }

        // set exclude page-list
        if ($settings['excludePidList']) {
            $excludePages = array_merge($excludePages,
                \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $settings['excludePidList'])
            );
        }

        return $excludePages;
    }

    /**
     * Gets list of pids to include
     *
     * @param array $settings
     * @return array
     */
    public static function getIncludePidList (array $settings)
    {

        $objectManager =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository */
        $pagesRepository = $objectManager->get(PagesRepository::class);

        $rootPidList = [1];
        $includePages = [];

        // if a pidList is set, we use this
        if ($settings['pidList']) {

            // if its marked as recursive, we have more to fetch
            if ($settings['pidListRecursive']) {
                $rootPidList = GeneralUtility::trimExplode(',', $settings['pidList']);
            } else {
                $includePages =  GeneralUtility::trimExplode(',', $settings['pidList']);
                return $includePages;
            }

        // if there is an existing starting pid we use this
        } else if (
            ($settings['startingPid'])
            && ($pagesRepository->findByIdentifier(intval($settings['startingPid'])))
        ) {
            $rootPidList = [intval($settings['startingPid'])];


        // if no startingPid is set, we can use a fallback-list, too
        } else if ($settings['startingPidList']) {
            $rootPidList = GeneralUtility::trimExplode(',', $settings['startingPidList']);
        }


        /** @var QueryGenerator $queryGenerator */
        $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);
        foreach ($rootPidList as $rootPid) {
            $includePages = array_merge(
                $includePages,
                GeneralUtility::trimExplode(',', $queryGenerator->getTreeList($rootPid, 9999, 0, 1), true)
            );
        }

        return $includePages;
    }



    /**
     * Gets the combined filter by name
     *
     * @param string $name
     * @param array $settings
     * @param array $externalFilter
     * @return array
     */
    public static function getCombinedFilterByName (string $name, array $settings, array $externalFilter = [])
    {

        if (!in_array($name, array_keys(self::VALID_FILTERS))) {
            return [];
        }

        // page property filters take precedence if defined
        if (
            ($pagePropertyFilter = self::getPagePropertyFilters($settings))
            && ($pagePropertyFilter[$name])
        ) {
            return [$pagePropertyFilter[$name]];
        }

        // take external filter (except there is nothing specific selected)
        $insecureValue = '';
        if (
            isset($externalFilter[$name]) &&
            $externalFilter[$name] != '0'
        ) {

            if (is_array($externalFilter[$name])) {
                $insecureValue = implode(',', $externalFilter[$name]);
            } else {
                $insecureValue = $externalFilter[$name];
            }

        // fallback to defined list
        } else if (isset($settings[$name . 'List'])) {
            $insecureValue = $settings[$name . 'List'];
        }

        return array_filter(
            GeneralUtility::trimExplode(
                ',',
                preg_replace('/[^0-9a-z,]+/', '', $insecureValue),
                true
            )
        );
    }


    /**
     * Gets the combined filter for department
     *
     * !! Important: The field "department" has an inverted logic !!
     * -> If no department is selected, to not use the default value as fallback!
     *
     * @param array  $settings
     * @param array  $externalFilter
     * @return array
     */
    public static function getCombinedFilterForDepartment (array $settings, array $externalFilter = [])
    {
        $name = "department";

        // page property filters take precedence if defined
        if (
            ($pagePropertyFilter = self::getPagePropertyFilters($settings))
            && ($pagePropertyFilter[$name])
        ) {
            return [$pagePropertyFilter[$name]];
        }

        // take external filter (except there is nothing specific selected)
        $insecureValue = '';
        if (
            $externalFilter[$name] != '0'
            && $externalFilter[$name] != null
        ) {

            if (is_array($externalFilter[$name])) {
                $insecureValue = implode(',', $externalFilter[$name]);
            } else {
                $insecureValue = $externalFilter[$name];
            }

            // ELSE: Do not use a predefined value by default here. Only if no external filter is given
        } else if (
            $externalFilter[$name] == null
            && isset($settings[$name . 'List'])
        ) {
            $insecureValue = $settings[$name . 'List'];
        }

        return array_filter(
            GeneralUtility::trimExplode(
                ',',
                preg_replace('/[^0-9a-z,]+/', '', $insecureValue),
                true
            )
        );
    }


    /**
     * Gets the filters based on existing page properties
     *
     * @param array settings
     * @return array
     */
    public static function getPagePropertyFilters (array $settings)
    {
        $objectManager =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository */
        $pagesRepository = $objectManager->get(PagesRepository::class);

        /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
        if ($page = $pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

            if (
                ($settingFilters = GeneralUtility::trimExplode(',', $settings['pagePropertyFilter']))
                && (is_array($settingFilters))
            ) {

                $filterList = [];
                foreach ($settingFilters as $filter) {
                    if (
                        (isset(self::VALID_FILTERS[$filter]))
                        && ($getter = self::VALID_FILTERS[$filter])
                        && (method_exists($page, $getter))
                        && ($result = $page->$getter())
                    ){

                        if ($result instanceof AbstractEntity) {
                            $filterList[$filter] = $result->getUid();
                        }
                    }
                }

                return $filterList;
            }
        }

        return [];
    }


    /**
     * Gets the project assignment recursively
     *
     * @return \RKW\RkwProjects\Domain\Model\Projects|null
     */
    public static function getPageProjectRecursive ()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {

            $objectManager =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository */
            $pagesRepository = $objectManager->get(PagesRepository::class);

            /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
            if ($page = $pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

                // current page has assignment - use this
                if ($page->getTxRkwprojectsProjectUid()) {
                    return $page->getTxRkwprojectsProjectUid();

                } else {

                    // Go recursive through the tree and search for a project assignment
                    $cnt = 0;
                    if ($page->getUid() > 1) {

                        do {
                            // Get parent
                            $page = $pagesRepository->findByIdentifier($page->getPid());
                            if (!$page) {
                                return null;
                            }

                            // Get project of parent, if set
                            if ($page->getTxRkwprojectsProjectUid()) {
                                return $page->getTxRkwprojectsProjectUid();
                            }

                            $cnt++;

                        } while (
                            $page->getUid() > 1
                            && $cnt < 100
                        );
                    }
                }
            }
        }

        return null;
    }

    /**
     * Gets the relevant sysCategories
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>|null
     */
    public static function getPageSysCategories ()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {

            $sysCategories = null;
            if ($project = self::getPageProjectRecursive()) {
                $sysCategories = $project->getSysCategory();
            }
        }

        if (
            (!$sysCategories)
            || (count($sysCategories) < 1)
        ) {

            $objectManager =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository */
            $pagesRepository = $objectManager->get(PagesRepository::class);

            /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
            if ($page = $pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {
                $sysCategories = $page->getSysCategory();
            }
        }

        return $sysCategories;
    }


    /**
     * Gets the project assignment recursively
     *
     * @return \RKW\RkwBasics\Domain\Model\Department|null
     */
    public static function getPageDepartmentRecursive ()
    {

        $objectManager =  \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository */
        $pagesRepository = $objectManager->get(PagesRepository::class);

        /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
        if ($page = $pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

            // current page has assignment - use this
            if ($page->getTxRkwbasicsDepartment()) {
                return $page->getTxRkwbasicsDepartment();

            } else {

                // Go recursive through the tree and search for a department assignment
                $cnt = 0;
                if ($page->getUid() > 1) {

                    do {
                        // Get parent
                        $page = $pagesRepository->findByIdentifier($page->getPid());
                        if (!$page) {
                            return null;
                        }

                        // Get department of parent, if set
                        if ($page->getTxRkwbasicsDepartment()) {
                            return $page->getTxRkwbasicsDepartment();
                        }

                        $cnt++;

                    } while (
                        $page->getUid() > 1
                        && $cnt < 100
                    );
                }
            }
        }

        return null;
    }


}