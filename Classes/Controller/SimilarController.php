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

use RKW\RkwProjects\Domain\Model\Projects;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class SimilarController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SimilarController extends AbstractController
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>|null
     */
    protected ?ObjectStorage $sysCategories = null;


    /**
     * @var \RKW\RkwProjects\Domain\Model\Projects|null
     */
    protected ?Projects $project = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\Pages>|null
     */
    protected ?ObjectStorage $relatedPages = null;


    /**
     * listAction
     *
     * @param int $pageNumber
     * @param int $ttContentUid
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @todo some users disable caching for no reason. So now we have performance issues
     */
    public function listAction(int $pageNumber = 0, int $ttContentUid = 0): void
    {
        // get plugins content element UID
        // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
        if (!$ttContentUid) {
            $ttContentUid = $this->ajaxHelper->getContentUid();
        }

        $pageNumber++;
        $itemsPerPage = 10;

        //  Set cache-identifiers
        /** @var \RKW\RkwRelated\Cache\ContentCache $contentCache */
        $contentCache = $this->getCache();
        $contentCache->setEntryIdentifier(
            $contentCache->generateEntryIdentifier(
                $ttContentUid,
                $pageNumber,
                $this->settings
            )
        );

        /** @var \RKW\RkwRelated\Cache\CountCache $countCache */
        $countCache = $this->getCache(true);
        $countCache->setEntryIdentifier(
            $contentCache->generateEntryIdentifier(
                $ttContentUid,
                $pageNumber,
                $this->settings
            )
        );

        if (
            ($contentCache->hasContent())
            && ($countCache->hasContent())
            && (!$this->settings['noCache'])
        ) {

            // Cache exists
            $relatedPages = $contentCache->getContent();
            $nextRelatedPagesCount = $countCache->getContent();

            $this->getLogger()->log(
                \TYPO3\CMS\Core\Log\LogLevel::INFO,
                sprintf(
                    'Plugin %s: Loading cached results for page %s.',
                    $this->request->getPluginName(),
                    intval($GLOBALS['TSFE']->id)
                )
            );

        } else {

            // Include & Exclude pages
            $excludePidList = $this->filterUtility::getExcludePidList($this->settings);
            $includePidList = $this->filterUtility::getIncludePidList($this->settings);

            // extended filtering
            $categories = $this->filterUtility::getPageSysCategories();
            $project = $this->filterUtility::getPageProjectRecursive();
            $department = $this->filterUtility::getPageDepartmentRecursive();

            // determine items per page
            if (is_array($this->settings['itemLimitPerPage'])) {

                $layout = strtolower($this->settings['layout'] ? $this->settings['layout'] : 'default');
                if ($this->settings['itemLimitPerPage'][$layout]) {
                    $itemsPerPage = intval($this->settings['itemLimitPerPage'][$layout]);
                }
                if ($this->settings['itemLimitPerPageOverride']) {
                    $itemsPerPage = intval($this->settings['itemLimitPerPageOverride']);
                }
            }


            // Check for sysCategories or project
            // if there are no sysCategories we check for pages that belong to the same project
            $relatedPages = [];
            $nextRelatedPages = [];
            if (
                ($categories)
                && (! $this->settings['noCategorySearch'])
                && count($categories)
            ) {

                /** @todo if language is not the standard one (= not "0"), use pagesLanguageOverlayRepository -- really needed? */
                $relatedPages = $this->pagesRepository->findBySysCategory(
                    $categories,
                    $excludePidList,
                    $this->settings['sysCategoryParentUid'],
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility']),
                    boolval($this->settings['hideMoreLink'])
                );

                if (!boolval($this->settings['hideMoreLink'])) {
                    $nextRelatedPages = $this->pagesRepository->findBySysCategory(
                        $categories,
                        $excludePidList,
                        $this->settings['sysCategoryParentUid'],
                        ($pageNumber + 1),
                        $itemsPerPage,
                        boolval($this->settings['ignoreVisibility'])
                    );
                }
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO,
                    sprintf(
                        'Plugin %s: Using category filter for page %s. Found %s pages.',
                        $this->request->getPluginName(),
                        intval($GLOBALS['TSFE']->id),
                        count($relatedPages)
                    )
                );
            }


            if (
                ($department)
                && (! $this->settings['noDepartmentSearch'])
                && (count($relatedPages) < 1)
            ){
                $relatedPages = $this->pagesRepository->findByDepartment(
                    $department,
                    $excludePidList,
                    $includePidList,
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility']),
                    boolval($this->settings['hideMoreLink'])
                );

                if (!boolval($this->settings['hideMoreLink'])) {
                    $nextRelatedPages = $this->pagesRepository->findByDepartment(
                        $department,
                        $excludePidList,
                        $includePidList,
                        ($pageNumber + 1),
                        $itemsPerPage,
                        boolval($this->settings['ignoreVisibility'])
                    );
                }

                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO,
                    sprintf(
                        'Plugin %s: Using department filter for page %s. Found %s pages.',
                        $this->request->getPluginName(),
                        intval($GLOBALS['TSFE']->id),
                        count($relatedPages)
                    )
                );
            }


            if (
                ($project)
                && (! $this->settings['noProjectSearch'])
                && (count($relatedPages) < 1)
            ){

                $relatedPages = $this->pagesRepository->findByProject(
                    $project,
                    $excludePidList,
                    $includePidList,
                    $pageNumber,
                    $itemsPerPage,
                    boolval($this->settings['ignoreVisibility']),
                    boolval($this->settings['hideMoreLink'])
                );

                if (!boolval($this->settings['hideMoreLink'])) {
                    $nextRelatedPages = $this->pagesRepository->findByProject(
                        $project,
                        $excludePidList,
                        $includePidList,
                        ($pageNumber + 1),
                        $itemsPerPage,
                        boolval($this->settings['ignoreVisibility'])
                    );
                }
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO,
                    sprintf(
                        'Plugin %s: Using project filter for page %s. Found %s pages.',
                        $this->request->getPluginName(),
                        intval($GLOBALS['TSFE']->id),
                        count($relatedPages)
                    )
                );
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
                $contentCache->setContent(
                    $relatedPages,
                    $cacheTtl
                );
                $countCache->setContent(
                    $nextRelatedPagesCount,
                    $cacheTtl
                );
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::INFO,
                    sprintf(
                        'Plugin %s: Caching results for page %s.',
                        $this->request->getPluginName(),
                        intval($GLOBALS['TSFE']->id)
                    )
                );

            } else {
                $this->getLogger()->log(
                    \TYPO3\CMS\Core\Log\LogLevel::WARNING,
                    sprintf(
                        'Plugin %s: No results found for page %s.',
                        $this->request->getPluginName(),
                        intval($GLOBALS['TSFE']->id)
                    )
                );
            }
        }

        $showMoreLink = !($nextRelatedPagesCount < 1) && !$this->settings['hideMoreLink'];

        $assignments = [
            'layout'                 => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
            'relatedPagesList'       => $relatedPages,
            'pageNumber'             => $pageNumber,
            'showMoreLink'           => $showMoreLink,
            'currentPluginName'      => $this->request->getPluginName(),
            'itemsPerPage'           => $itemsPerPage,
            'linkInSameWindow'       => ($this->settings['openLinksInSameWindowOverride'] ?? $this->settings['openLinksInSameWindow'])
        ];

        $this->view->assignMultiple($assignments);
    }
}
