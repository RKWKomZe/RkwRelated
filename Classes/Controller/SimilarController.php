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
        }

        $pageNumber++;
        $limit = 8;

        $this->contentCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber);
        // $this->countCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber);

        if (
            ($this->contentCache->hasContent())
            // && ($this->countCache->hasContent())
        ) {

            // Cache exists
            $relatedPages = $this->contentCache->getContent();
            // $limit = $this->countCache->getContent();

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Loading cached results for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));

        } else {

            // Include & Exclude pages
            $excludePidList = $this->filterUtility->getExcludePidList($this->settings);
            $includePidList = $this->filterUtility->getIncludePidList($this->settings);

            // extended filtering
            $categories = $this->filterUtility->getSysCategories();
            $project = $this->filterUtility->getProjectRecursive();
            $department = $this->filterUtility->getDepartmentRecursive();
            $limit = ($this->settings['minItems'] ? intval($this->settings['minItems']) : 8);

            /*
             * @toDo: This won't work with gridElements
            // calculate item count
            $ttContentList = $this->ttContentRepository->findBodyTextElementsByPage($page, $currentSysLanguageUid, $this->settings);

            // set initial some value as fallback if $this->settings['itemsPerHundredSigns']) is not set
            if (floatval($this->settings['itemsPerHundredSigns'])) {

                $fullTextLength = 0;
                /** @var \RKW\RkwRelated\Domain\Model\TtContent $ttContentElement
                foreach ($ttContentList as $ttContentElement) {
                    $fullTextLength += strlen(strip_tags($ttContentElement->getBodytext()));
                }

                $this->limit = intval(floor($fullTextLength / 100 * floatval($this->settings['itemsPerHundredSigns'])));

                if (($this->limit < intval($this->settings['minItems'])) && (intval($this->settings['minItems']))) {
                    $this->limit = intval($this->settings['minItems']);
                }
            }
            */

            /** @deprecated */
            $this->settings['itemsPerHundredSigns'] = PHP_INT_MAX;

            // Check for sysCategories or project
            // if there are no sysCategories we check for pages that belong to the same project
            $relatedPages = [];
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
                    $limit,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using category filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
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
                    $limit,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using project filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
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
                    $limit,
                    boolval($this->settings['ignoreVisibility'])
                );

                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Using department filter for page %s. Found %s pages.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id), count($relatedPages)));
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
                /*$this->countCache->setContent(
                    $limit,
                    $cacheTtl
                );*/
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Caching results for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));

            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Plugin %s: No results found for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));
            }
        }

        /** New version */
        if ($this->settings['version'] == 2) {

            $assignments = [
                'layout'                      => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
                'relatedPagesList'            => $relatedPages,
                'pageNumber'                  => $pageNumber,
                'currentPluginName'           => $this->request->getPluginName(),
                'limit'                       => $limit,
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
                'limit'                       => $limit,
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