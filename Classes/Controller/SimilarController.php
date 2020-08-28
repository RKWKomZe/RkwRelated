<?php

namespace RKW\RkwRelated\Controller;

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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class SimilarController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SimilarController extends AbstractController
{

    /**
     * sysCategories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>
     */
    protected $sysCategories = null;

    /**
     * project
     *
     * @var \RKW\RkwProjects\Domain\Model\Projects
     */
    protected $project = null;

    /**
     * relatedPages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\Pages>
     */
    protected $relatedPages = null;


    /**
     * listAction
     *
     * @param integer $pageNumber
     * @param integer $ttContentUid
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function listAction($pageNumber = 0, $ttContentUid = 0)
    {
        // get plugins content element UID
        // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
        if (!$ttContentUid) {
            $ttContentUid = $this->ajaxHelper->getContentUid();

        /** @deprecated - making old version work with new ajax */
        } else if ($ttContentUid) {
            $this->ajaxHelper->init(['cid' => $ttContentUid]);
            $this->loadSettingsFromFlexForm();
        }

        $pageNumber++;
        $itemsPerPage = 10;

        $this->contentCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber);
        $this->countCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber);

        if (
            ($this->contentCache->hasContent())
            && ($this->countCache->hasContent())
            && (!$this->settings['noCache'])
        ) {

            // Cache exists
            $relatedPages = $this->contentCache->getContent();
            $nextRelatedPagesCount = $this->countCache->getContent();

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Loading cached results for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));

        } else {

            // Include & Exclude pages
            $excludePidList = $this->filterUtility::getExcludePidList($this->settings);
            $includePidList = $this->filterUtility::getIncludePidList($this->settings);

            // extended filtering
            $categories = $this->filterUtility::getPageSysCategories();
            $project = $this->filterUtility::getPageProjectRecursive();
            $department = $this->filterUtility::getPageDepartmentRecursive();

            // determine items per page
            /** new version */
            if ($this->settings['version'] == 2) {
                if (is_array($this->settings['itemLimitPerPage'])) {

                    $layout = strtolower($this->settings['layout'] ? $this->settings['layout'] : 'default');
                    if ($this->settings['itemLimitPerPage'][$layout]) {
                        $itemsPerPage = intval($this->settings['itemLimitPerPage'][$layout]);
                    }
                }

            /** @deprecated old version*/
            } else {
                $itemsPerPage = ($this->settings['minItems'] ? intval($this->settings['minItems']) : 8);
                $this->settings['itemsPerHundredSigns'] = PHP_INT_MAX;
            }


            // Check for sysCategories or project
            // if there are no sysCategories we check for pages that belong to the same project
            $relatedPages = [];
            $nextRelatedPages = [];
            if (
                ($categories)
                && count($categories)
            ) {

                /** @toDo: if language is not the standard one (= not "0"), use pagesLanguageOverlayRepository -- really needed? */
                $relatedPages = $this->pagesRepository->findBySysCategory(
                    $categories,
                    $excludePidList,
                    $this->settings['sysCategoryParentUid'],
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $nextRelatedPages = $this->pagesRepository->findBySysCategory(
                    $categories,
                    $excludePidList,
                    $this->settings['sysCategoryParentUid'],
                    ($pageNumber+1),
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using category filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
            }


            if (
                ($department)
                && (count($relatedPages) < 1)
            ){
                $relatedPages = $this->pagesRepository->findByDepartment(
                    $department,
                    $excludePidList,
                    $includePidList,
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $nextRelatedPages = $this->pagesRepository->findByDepartment(
                    $department,
                    $excludePidList,
                    $includePidList,
                    ($pageNumber+1),
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using department filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
            }


            if (
                ($project)
                && (count($relatedPages) < 1)
            ){

                $relatedPages = $this->pagesRepository->findByProject(
                    $project,
                    $excludePidList,
                    $includePidList,
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $nextRelatedPages = $this->pagesRepository->findByProject(
                    $project,
                    $excludePidList,
                    $includePidList,
                    ($pageNumber+1),
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using project filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
            }

            // get available items for next page
            $nextRelatedPagesCount = 0;
            if ($nextRelatedPages) {
                $nextRelatedPagesCount = count($nextRelatedPages);
            }

            if (
                ($relatedPages)
                && (count($relatedPages) > 0)
            ) {

                $cacheTtl = $this->settings['cache']['ttl'] ? $this->settings['cache']['ttl'] : 86400;
                $this->contentCache->setContent(
                    $relatedPages,
                    $cacheTtl
                );
                $this->countCache->setContent(
                    $nextRelatedPagesCount,
                    $cacheTtl
                );
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Caching results for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Plugin %s: No results found for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));
            }
        }

        $showMoreLink = ($nextRelatedPagesCount < 1) ? false : !boolval($this->settings['hideMoreLink']);

        /** New version */
        if ($this->settings['version'] == 2) {

            $assignments = [
                'layout'                      => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
                'relatedPagesList'            => $relatedPages,
                'pageNumber'                  => $pageNumber,
                'showMoreLink'                => $showMoreLink,
                'currentPluginName'           => $this->request->getPluginName(),
                'itemsPerPage'                => $itemsPerPage,
            ];


            $this->view->assignMultiple($assignments);

        /** @depreacted  */
        } else {


            $assignments = [
                'layout'                      => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
                'relatedPagesList'            => $relatedPages,
                'pageNumber'                  => $pageNumber,
                'currentPluginName'           => $this->request->getPluginName(),
                'currentPluginNameStrtolower' => strtolower($this->request->getPluginName()),
                'pageTypeAjax'                => intval($this->settings['pageTypeAjaxSimilarcontent']),
                'itemsPerHundredSigns'        => floatval($this->settings['itemsPerHundredSigns']), // do not load float value in view -> this can produce ajax issues. Or write a ViewHelper ;-)
                'limit'                       => $itemsPerPage,
                'settingsArray'               => $this->settings, // do not access settings in view the normal way -> this would produce ajax issues
                'ttContentUid'                => $ttContentUid
            ];


            // Choose kind of view. Either normal templating, or its ajax-more functionality
            // Use normal view on pageNumber == 1
            // Else use ajax api for more items
            if ($pageNumber == 1 || $this->isAjaxCall() == false) {

                $this->view->assignMultiple($assignments);

            } else {

                // get JSON helper
                /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
                $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');

                $jsonHelper->setHtml(
                    'tx-rkwrelated-result-section-' . strtolower($this->request->getPluginName()),
                    $assignments,
                    'append',
                    'Ajax/List/Similar.html'
                );

                print (string)$jsonHelper;
                exit();
            }
        }
    }
}