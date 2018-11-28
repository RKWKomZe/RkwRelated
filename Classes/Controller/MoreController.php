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
class MoreController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * year
     * start date for filter select
     *
     * @var int
     */
    protected $year = 2011;

    /**
     * pagesRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\PagesRepository
     * @inject
     */
    protected $pagesRepository = null;

    /**
     * pagesLanguageOverlayRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\PagesLanguageOverlayRepository
     * @inject
     */
    protected $pagesLanguageOverlayRepository = null;

    /**
     * ttContentRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\TtContentRepository
     * @inject
     */
    protected $ttContentRepository = null;

    /**
     * relatedPages
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\Pages>
     */
    protected $relatedPages = null;

    /**
     * departmentRepository
     *
     * @var \RKW\RkwBasics\Domain\Repository\DepartmentRepository
     * @inject
     */
    protected $departmentRepository = null;

    /**
     * documentTypeRepository
     *
     * @var \RKW\RkwBasics\Domain\Repository\DocumentTypeRepository
     * @inject
     */
    protected $documentTypeRepository = null;

    /**
     * cacheManager
     *
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    protected $cacheManager;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj;


    public function initializeAction()
    {
        $this->cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache("rkw_related");
        $this->cObj = $this->configurationManager->getContentObject();

        // optional overwrite of year by TypoScript (used as filter option in template)
        $this->year = $this->settings['staticInitialYearForFilter'] ? intval($this->settings['staticInitialYearForFilter']) : $this->year;
    }

    /**
     * listAction
     *
     * @param array $filter
     * @param integer $pageNumber
     * @param integer $ttContentUid
     */
    public function listAction($filter = array(), $pageNumber = 0, $ttContentUid = 0)
    {
        $pageNumber++;

        // if it's a filter request for new content (filter is also set in case of "more" functionality)
        $isFilterRequest = false;
        if ($filter && $pageNumber <= 1) {
            $isFilterRequest = true;
        }

        // check for AJAX
        $isAjaxCall = false;
        if (
            \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContent'])
            || \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContent2'])
            || \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContentPublication'])
        ) {
            $isAjaxCall = true;
        }

        // 1.1 get plugins content element UID
        // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
        if (!$ttContentUid) {
            $ttContentUid = intval($this->configurationManager->getContentObject()->data['uid']);
        }

        // 1.2 in ajax context we need to grab the flexform settings through the tt_content repository
        // (Common TS-settings are still available)
        /** @toDo: Obviously we also need this when a plugin is inherited to subpages. Otherwise changes in the flexform are not inherited to the subpages * */
        //if ($isAjaxCall) {
        foreach ($this->ttContentRepository->findFlexformDataByUid($ttContentUid, $this->request->getPluginName()) as $settingKey => $settingValue) {
            $this->settings[str_replace('settings.', '', $settingKey)] = $settingValue;
        }
        //}

        // 1.3 Get language of user
        $currentSysLanguageUid = intval($GLOBALS['TSFE']->config['config']['sys_language_uid']);

        // 1.4 Check cache for PID + tt_content element and page number. If there is something, we don't need to search something via SQL
        $cacheIdentifier = intval($GLOBALS['TSFE']->id) . '_' . $ttContentUid . '_' . $currentSysLanguageUid . '_rkwrelated_more_' . strtolower($this->request->getPluginName()) . '_' . intval($pageNumber);

        // Current state: No caching if someone is filtering via frontend form
        if (
            \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()
            && ($this->cacheManager->has($cacheIdentifier))
            && ($this->cacheManager->has($cacheIdentifier . '_count'))
            && (!$filter)
            && (!$this->settings['noCache'])
        ) {
            // Cache exists
            $this->relatedPages = $this->cacheManager->get($cacheIdentifier);
            $fullResultCount = $this->cacheManager->get($cacheIdentifier . '_count');

        } else {
            // Cache does not exist or we're in development context

            // if a special startingPid is set, set it as rootPid
            $rootPid = 0; // $GLOBALS['TSFE']->rootLine[0]['uid'];
            $excludePages = array(intval($GLOBALS['TSFE']->id));
            if (
                ($this->settings['startingPid'])
                && ($this->pagesRepository->findByIdentifier(intval($this->settings['startingPid'])))
            ) {
                $excludePages[] = $rootPid = intval($this->settings['startingPid']);

                // if nothing is set, we can use a fallback-list, too
            } else {
                if ($this->settings['startingPidList']) {
                    $rootPid = $this->settings['startingPidList'];
                }
            }


            // Only pages of a certain root PID or based on the given list!
            /** @var \TYPO3\CMS\Frontend\Plugin\AbstractPlugin $pi */
            $pi = $this->objectManager->get('TYPO3\CMS\Frontend\Plugin\AbstractPlugin');
            $pi->cObj = $this->configurationManager->getContentObject();
            $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pi->pi_getPidList($rootPid, 9999));
            if ($this->settings['pidList']) {
                $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['pidList']);
                if ($this->settings['pidListRecursive']) {
                    $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pi->pi_getPidList($this->settings['pidList'], 9999));
                }
            }

            // set exclude page-list
            if ($this->settings['excludePidList']) {
                $excludePages = array_merge($excludePages, \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['excludePidList']));
            }

            // 2 If RkwProjects is installed && project is set:
            // 2.1 Get linked categories of the project of the page
            $flexformConfigurationType = 'findByConfiguration';
            if (
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')
                && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')
            ) {
                $flexformConfigurationType = 'findByConfigurationFullSpectrum';
            }

            // Get the related pages
            $this->relatedPages = $this->pagesRepository->$flexformConfigurationType($this->settings, $excludePages, $pageNumber, $pidList, $filter, $this->request->getPluginName());
            $fullResultCount = $this->pagesRepository->$flexformConfigurationType($this->settings, $excludePages, null, $pidList, $filter, $this->request->getPluginName());

            // 3. Cache it!
            if (count($this->relatedPages) > 0) {
                $cacheTtl = $this->settings['cache']['ttl'] ? $this->settings['cache']['ttl'] : 86400;
                $this->cacheManager->set(
                    $cacheIdentifier,
                    $this->relatedPages,
                    array(
                        'tx_rkwrelated',
                        'tx_rkwrelated_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_more',
                        'tx_rkwrelated_more_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_more_' . strtolower($this->request->getPluginName()),
                        'tx_rkwrelated_more_' . strtolower($this->request->getPluginName()) . '_' . intval($GLOBALS['TSFE']->id),
                    ),
                    $cacheTtl
                );
                $this->cacheManager->set(
                    $cacheIdentifier . '_count',
                    $fullResultCount,
                    array(
                        'tx_rkwrelated',
                        'tx_rkwrelated_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_more',
                        'tx_rkwrelated_more_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_more_' . strtolower($this->request->getPluginName()),
                        'tx_rkwrelated_more_' . strtolower($this->request->getPluginName()) . '_' . intval($GLOBALS['TSFE']->id),
                    ),
                    $cacheTtl
                );
            }
        }

        $moreItemsAvailable = ($pageNumber * $this->settings['itemsPerPage']) < count($fullResultCount) ? true : false;
        if (intval($this->settings['maximumShownResults'])) {
            $showMoreLink = ($pageNumber * $this->settings['itemsPerPage']) < intval($this->settings['maximumShownResults']) ? true : false;
        } else {
            $this->settings['maximumShownResults'] = PHP_INT_MAX;
            $showMoreLink = true;
        }

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

        // to avoid dependence to RkwProject, we're calling the repository this way
        $projectList = null;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            /** @var \RKW\RkwProjects\Domain\Repository\ProjectsRepository $projectsRepository */
            $projectsRepository = $objectManager->get('RKW\\RkwProjects\\Domain\\Repository\\ProjectsRepository');
            $projectList = $projectsRepository->findAllByVisibility();
        }

        // 4. Choose kind of view. Either normal templating, or its ajax-more functionallity
        // Use normal view on page 1. Else use ajax api for more items
        // But if no ajax is used on a further page (robots eg), use normal view
        if (
            ($pageNumber == 1)
            && ($isAjaxCall == false)
            // && !$isFilterRequest
        ) {
            $this->view->assign('relatedPagesList', $this->relatedPages);
            $this->view->assign('pageNumber', $pageNumber);
            $this->view->assign('pageTypeAjax', $pageTypeAjax);
            // do not access settings in view the normal way -> this would produce ajax issues
            $this->view->assign('settingsArray', $this->settings);
            $this->view->assign('ttContentUid', $ttContentUid);
            $this->view->assign('showMoreLink', $showMoreLink);
            $this->view->assign('moreItemsAvailable', $moreItemsAvailable);
            $this->view->assign('currentPluginName', $this->request->getPluginName());
            $this->view->assign('currentPluginNameStrtolower', strtolower($this->request->getPluginName()));
            $this->view->assign('departmentList', $this->departmentRepository->findAllByVisibility());
            $this->view->assign('documentTypeList', $this->documentTypeRepository->findAllByTypeAndVisibility('publications'));
            $this->view->assign('projectList', $projectList);
            $this->view->assign('years', array_combine(range($this->year, date("Y")), range($this->year, date("Y"))));
            $this->view->assign('filter', $filter);
            $this->view->assign('sysLanguageUid', intval($GLOBALS['TSFE']->config['config']['sys_language_uid']));

        } else {

            // get JSON helper
            /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
            $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');

            // get new list
            $kindOfRequest = $isFilterRequest ? 'replace' : 'append';
            $divId = $isFilterRequest ? 'tx-rkwrelated-result-section-' . strtolower($this->request->getPluginName()) . '-' . $ttContentUid : 'tx-rkwrelated-boxes-grid-' . strtolower($this->request->getPluginName()) . '-' . $ttContentUid;
            $replacements = array(
                'relatedPagesList'            => $this->relatedPages,
                'pageNumber'                  => $pageNumber,
                'pageTypeAjax'                => $pageTypeAjax,
                'settingsArray'               => $this->settings,
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
                'requestType'                 => $kindOfRequest,
            );

            $jsonHelper->setHtml(
                $divId,
                $replacements,
                $kindOfRequest,
                'Ajax/List/More.html'
            );

            print (string)$jsonHelper;
            exit();
            //===
        }
    }
}