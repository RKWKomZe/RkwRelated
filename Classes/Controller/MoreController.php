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

/**
 * Class MoreController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MoreController extends AbstractController
{

    /**
     * year
     * start date for filter select
     *
     * @var int
     */
    protected $year = 2011;



    public function initializeAction()
    {
        // optional overwrite of year by TypoScript (used as filter option in template)
        $this->year = $this->settings['staticInitialYearForFilter'] ? intval($this->settings['staticInitialYearForFilter']) : $this->year;
    }


    /**
     * listAction
     *
     * @param array $filter
     * @param integer $pageNumber
     * @param integer $ttContentUid
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function listAction($filter = array(), $pageNumber = 0, $ttContentUid = 0)
    {

        // get plugins content element UID
        // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
        if (!$ttContentUid) {
            $ttContentUid = $this->ajaxHelper->getContentUid();

        /** @deprecated - making old version work with new ajax */
        } else if ($ttContentUid) {
            $this->ajaxHelper->setContentUid($ttContentUid);
            $this->loadSettingsFromFlexForm();
        }

        $pageNumber++;

        // if it's a filter request for new content (filter is also set in case of "more" functionality)
        $isFilterRequest = false;
        if ($filter && $pageNumber <= 1) {
            $isFilterRequest = true;
        }

        // get advanced filters
        $filterList = [
            'documentType' => $this->filterUtility::getCombinedFilterByName(
                'documentType',
                $this->settings,
                $filter
            ),
            'department' => $this->filterUtility::getCombinedFilterForDepartment(
                $this->settings,
                $filter
            ),
            'project' => $this->filterUtility::getCombinedFilterByName(
                'project',
                $this->settings,
                $filter
            ),
            'year' => intval($filter['year'])
        ];

        //  Set cache-identifiers
        $this->contentCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber, array_merge($this->settings, $filter));
        $this->countCache->setIdentifier($this->request->getPluginName(), $ttContentUid, $pageNumber, array_merge($this->settings, $filter));

        // Current state: No caching if someone is filtering via frontend form
        if (
           ($this->contentCache->hasContent())
            && ($this->countCache->hasContent())
            && (!$filter)
            && (!$this->settings['noCache'])
        ) {
            // Cache exists
            $relatedPages = $this->contentCache->getContent();
            $nextRelatedPagesCount = $this->countCache->getContent();

            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Plugin %s: Loading cached results for page %s.', $this->request->getPluginName(), intval($GLOBALS['TSFE']->id)));

        } else {

            // determine items per page
            $itemsPerPage = 10;

            /** new version */
            if ($this->settings['version'] == 2) {
                if (is_array($this->settings['itemLimitPerPage'])) {

                    $layout = strtolower($this->settings['layout'] ? $this->settings['layout'] : 'default');
                    if ($this->settings['itemLimitPerPage'][$layout]) {
                        $itemsPerPage = intval($this->settings['itemLimitPerPage'][$layout]);
                    }
                    if ($this->settings['itemLimitPerPageOverride']) {
                        $itemsPerPage = intval($this->settings['itemLimitPerPageOverride']);
                    }
                    if (
                        ($this->settings['itemsPerPage'])
                        && (intval($this->settings['itemsPerPage']) <  $itemsPerPage)
                    ){
                        $itemsPerPage = intval($this->settings['itemsPerPage']);
                    }
                }

            /** @deprecated old version*/
            } else {
                if ($this->settings['itemsPerPage']) {
                    $itemsPerPage = intval($this->settings['itemsPerPage']);
                }
            }

            // Include & Exclude pages
            $excludePidList = $this->filterUtility::getExcludePidList($this->settings);
            $includePidList = $this->filterUtility::getIncludePidList($this->settings);

            // settings for publications including fallback to old solution
            $findPublications = intval($this->settings['displayPublications']);

            /** @deprecated old setting */
            if (
                ($this->settings['everythingWithoutPublications'])
                || ($this->request->getPluginName() == 'Morecontentpublication')
            ){
                $findPublications = ($this->request->getPluginName() == 'Morecontentpublication' ? 1 : ($this->settings['everythingWithoutPublications'] ? 2 : 0));
            }

            // get pages
            $relatedPages = $this->pagesRepository->findByConfiguration(
                $excludePidList,
                $includePidList,
                $filterList,
                $findPublications,
                $pageNumber,
                $itemsPerPage,
                boolval($this->settings['ignoreVisibility'])
            );

            $nextRelatedPages = $this->pagesRepository->findByConfiguration(
                $excludePidList,
                $includePidList,
                $filterList,
                $findPublications,
                ($pageNumber+1),
                $itemsPerPage,
                boolval($this->settings['ignoreVisibility'])
            );

            // get available items for next page
            $nextRelatedPagesCount = 0;
            if ($nextRelatedPages) {
                $nextRelatedPagesCount = count($nextRelatedPages);
            }

            // Cache it!
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
        /** @deprecated completely obsolete in version 2
        if (intval($this->settings['maximumShownResults'])) {
            $showMoreLink = ($pageNumber * $itemsPerPage) < intval($this->settings['maximumShownResults']) ? true : false;
        } else {
            $this->settings['maximumShownResults'] = PHP_INT_MAX;
            $showMoreLink = true;
        }*/

        /** @deprecated */
        $moreItemsAvailable = $showMoreLink;
        $this->settings['maximumShownResults'] = PHP_INT_MAX;
        $this->settings['showMoreLink'] = $showMoreLink;


        // to avoid dependence to RkwProject, we're calling the repository this way
        $projectList = null;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            /** @var \RKW\RkwProjects\Domain\Repository\ProjectsRepository $projectsRepository */
            $projectsRepository = $objectManager->get('RKW\\RkwProjects\\Domain\\Repository\\ProjectsRepository');
            $projectList = $projectsRepository->findAllByVisibility();
        }

        /** New version */
        if ($this->settings['version'] == 2) {

            $assignments = [
                'layout'                 => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
                'relatedPagesList'       => $relatedPages,
                'pageNumber'             => $pageNumber,
                'showMoreLink'           => $showMoreLink,
                'currentPluginName'      => $this->request->getPluginName(),
                'departmentList'         => $this->departmentRepository->findAllByVisibility(),
                'documentTypeList'       => $this->documentTypeRepository->findAllByTypeAndVisibility('publications'),
                'projectList'            => $projectList,
                'years'                  => array_combine(range($this->year, date("Y")), range($this->year, date("Y"))),
                'filter'                 => $filter,
                'filterFull'             => $filterList,
                'sysLanguageUid'         => intval($GLOBALS['TSFE']->config['config']['sys_language_uid']),
                'linkInSameWindow'       => (isset($this->settings['openLinksInSameWindowOverride']) ? $this->settings['openLinksInSameWindowOverride'] : $this->settings['openLinksInSameWindow'])
            ];

            $this->view->assignMultiple($assignments);

        /** @depreacted  */
        } else {

            // get correct typeNum of plugin
            $pageTypeAjax = '';
            if ($this->request->getPluginName() == "Morecontent") {
                $pageTypeAjax = intval($this->settings['pageTypeAjaxMoreContent']);
            }
            if ($this->request->getPluginName() == "Morecontent2") {
                $pageTypeAjax = intval($this->settings['pageTypeAjaxMoreContent2']);
            }
            if ($this->request->getPluginName() == "Morecontentpublication") {
                $pageTypeAjax = intval($this->settings['pageTypeAjaxMoreContentPublication']);
            }

            // Choose kind of view. Either normal templating, or its ajax-more functionality
            // Use normal view on page 1. Else use ajax api for more items
            // But if no ajax is used on a further page (robots eg), use normal view
            $assignments = [
                'relatedPagesList'            => $relatedPages,
                'pageNumber'                  => $pageNumber,
                'pageTypeAjax'                => $pageTypeAjax,
                'settingsArray'               => $this->settings,  // do not access settings in view the normal way -> this would produce ajax issues
                'ttContentUid'                => $ttContentUid,
                'showMoreLink'                => $showMoreLink,
                'moreItemsAvailable'          => $moreItemsAvailable,
                'currentPluginName'           => $this->request->getPluginName(),
                'currentPluginNameStrtolower' => strtolower($this->request->getPluginName()),
                'departmentList'              => $this->departmentRepository->findAllByVisibility(),
                'documentTypeList'            => $this->documentTypeRepository->findAllByTypeAndVisibility('publications'),
                'projectList'                 => $projectList,
                'years'                       => array_combine(range($this->year, date("Y")), range($this->year, date("Y"))),
                'filter'                      => $filter,
                'sysLanguageUid'              => intval($GLOBALS['TSFE']->config['config']['sys_language_uid']),
            ];

            if (
                ($pageNumber == 1)
                && (!$this->isAjaxCall())
                // && !$isFilterRequest
            ) {

                $this->view->assignMultiple($assignments);

            } else {

                // get JSON helper
                /** @var \RKW\RkwAjax\Encoder\JsonTemplateEncoder $jsonHelper */
                $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');

                // get new list
                $assignments['requestType'] = $kindOfRequest = $isFilterRequest ? 'replace' : 'append';
                $divId = $isFilterRequest ? 'tx-rkwrelated-result-section-' . strtolower($this->request->getPluginName()) . '-' . $ttContentUid : 'tx-rkwrelated-boxes-grid-' . strtolower($this->request->getPluginName()) . '-' . $ttContentUid;

                $jsonHelper->setHtml(
                    $divId,
                    $assignments,
                    $kindOfRequest,
                    'Ajax/List/More.html'
                );

                print (string)$jsonHelper;
                exit();
            }
        }
    }

}
