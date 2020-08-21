<?php

namespace RKW\RkwRelated\Utilities;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\QueryGenerator;
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
     * pagesRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;


    /**
     * Gets list pids to exclude
     *
     * @param array $settings
     * @return array
     */
    public function getExcludePidList(array $settings)
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
    public function getIncludePidList (array $settings)
    {

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
            && ($this->pagesRepository->findByIdentifier(intval($settings['startingPid'])))
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
     * @param array settings
     * @param array $externalFilter
     * @return array
     */
    public function getCombinedFilterByName ($name, array $settings, array $externalFilter = [])
    {

        $insecureValue = '';
        if (isset($externalFilter[$name])) {
            $insecureValue = $externalFilter[$name];

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
     * Gets the project assignment recursively
     *
     * @return \RKW\RkwProjects\Domain\Model\Projects|null
     */
    public function getProjectRecursive ()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {

            /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
            if ($page = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

                // current page has assignment - use this
                if ($page->getTxRkwprojectsProjectUid()) {
                    return $page->getTxRkwprojectsProjectUid();

                } else {

                    // Go recursive through the tree and search for a project assignment
                    $cnt = 0;
                    if ($page->getUid() > 1) {

                        do {
                            // Get parent
                            $page = $this->pagesRepository->findByIdentifier($page->getPid());
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
    public function getSysCategories ()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {

            $sysCategories = null;
            if ($project = $this->getProjectRecursive()) {
                $sysCategories = $project->getSysCategory();
            }
        }

        if (
            (!$sysCategories)
            || (count($sysCategories) < 1)
        ) {

            /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
            if ($page = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {
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
    public function getDepartmentRecursive ()
    {


        /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
        if ($page = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

            // current page has assignment - use this
            if ($page->getTxRkwbasicsDepartment()) {
                return $page->getTxRkwbasicsDepartment();

            } else {

                // Go recursive through the tree and search for a department assignment
                $cnt = 0;
                if ($page->getUid() > 1) {

                    do {
                        // Get parent
                        $page = $this->pagesRepository->findByIdentifier($page->getPid());
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