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
 * Class SimilarController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SimilarController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
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
     * department
     *
     * @var \RKW\RkwBasics\Domain\Model\Department
     */
    protected $department = null;

    /**
     * limit
     *
     * @var integer
     */
    protected $limit = 5;

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

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
        //===
    }

    public function initializeAction()
    {
        $this->cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache("rkw_related");
        $this->cObj = $this->configurationManager->getContentObject();
    }

    /**
     * listAction
     *
     * @param integer $pageNumber
     * @param integer $ttContentUid
     */
    public function listAction($pageNumber = 0, $ttContentUid = 0)
    {
        $pageNumber++;

        // check for AJAX
        $isAjaxCall = false;
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxSimilarcontent'])) {
            $isAjaxCall = true;
        }

        // 1. Get current page
        /** @var \RKW\RkwRelated\Domain\Model\Pages $page */
        if ($page = $this->pagesRepository->findByIdentifier(intval($GLOBALS['TSFE']->id))) {

            // 1.1 Get language of user
            $currentSysLanguageUid = intval($GLOBALS['TSFE']->config['config']['sys_language_uid']);

            // 1.2 Check cache for PID. If there is something, we don't need to search something via SQL
            $cacheIdentifier = $page->getUid() . '_' . $currentSysLanguageUid . '_rkwrelated_similar' . '_' . intval($pageNumber);
            if (
                \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext()->isProduction()
                && $this->cacheManager->has($cacheIdentifier)
                && $this->cacheManager->has($cacheIdentifier . '_limit')
            ) {
                // Cache exists
                $this->relatedPages = $this->cacheManager->get($cacheIdentifier);
                $this->limit = $this->cacheManager->get($cacheIdentifier . '_limit');

                if ($this->relatedPages === false) {
                    // if value is "false"
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('Fetched results from cache, but cache is empty!'));
                } else {
                    $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('Fetched %s results from cache.', count($this->relatedPages)));
                }


            } else {

                // Cache does not exist or we're in development context
                // 2 get plugins content element UID
                // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
                if (!$ttContentUid) {
                    $ttContentUid = intval($this->configurationManager->getContentObject()->data['uid']);
                }

                // 2.1 in ajax context we need to grab the flexform settings through the tt_content repository
                // (Common TS-settings are still available)
                /** @toDo: Obviously we also need this when a plugin is inherited to subpages. Otherwise changes in the flexform are not inherited to the subpages * */
                //if ($isAjaxCall) {
                foreach ($this->ttContentRepository->findFlexformDataByUid($ttContentUid, $this->request->getPluginName()) as $settingKey => $settingValue) {
                    $this->settings[str_replace('settings.', '', $settingKey)] = $settingValue;
                }
                //}

                // 2.3 Set root-page
                $rootPid = 0; // $GLOBALS['TSFE']->rootLine[0]['uid'];
                $excludePages = array($page->getUid());
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

                // 2.4 set exclude pages
                if ($this->settings['excludePidList']) {
                    $excludePages = array_merge($excludePages, \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['excludePidList']));
                }


                // 3 If RkwProjects is installed && project is set:
                // 3.1 Get linked categories of the project of the page and also store the project as fallback
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {

                    if ($page->getTxRkwprojectsProjectUid()) {
                        // 3.1.1: Get project by given page
                        $this->project = $page->getTxRkwprojectsProjectUid();
                    } else {

                        // 3.1.2: Get project by parentPage of given page (kind of fallback, if there is no project set)

                        // Go recursive through the tree and search for a project assignment
                        $runs = 0;
                        if ($page->getUid() > 1) {
                            /** @var \RKW\RkwRelated\Domain\Model\Pages $currentPage */
                            $currentPage = $page;

                            do {
                                // 1. Get parent
                                $currentParent = $this->pagesRepository->findByIdentifier($currentPage->getPid());
                                if (!$currentParent) {
                                    break;
                                    //===
                                }

                                // 2. Get project of parent, if set
                                if ($currentParent->getTxRkwprojectsProjectUid()) {
                                    $this->project = $currentParent->getTxRkwprojectsProjectUid();
                                }

                                // 3. Prepare for next round: Climb up to parent page
                                $currentPage = $currentParent;

                                $runs++;
                            } while (
                                $currentParent->getUid() > 1
                                && !$this->project
                                && $runs < 100
                            );
                        }
                    }
                }

                if ($this->project) {

                    $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
                    /** @var \RKW\RkwProjects\Domain\Repository\ProjectsRepository $projectsRepository */
                    $projectsRepository = $objectManager->get('RKW\\RkwProjects\\Domain\\Repository\\ProjectsRepository');

                    // 3.2 get project data by $page->getTxRkwprojectsProjectUid()
                    /** @var \RKW\RkwProjects\Domain\Model\Projects $projects */
                    $projects = $projectsRepository->findByIdentifier($this->project->getUid());

                    // 3.3 get sys_category of project data (we get an object storage here!)
                    $this->sysCategories = $projects->getSysCategory();
                }

                // 4 If RkwProjects is not NOT installed:
                // 4.1 Get category of page and search for other Pages
                // This is the point we walk along, if the rkw_project extension is not loaded. But if we don't get some sysCategories
                // in the step before, we go in here anyway (so we don't have to ask for "is-not-loaded"-('rkw_projets'))
                if (!count($this->sysCategories)) {
                    $this->sysCategories = $page->getSysCategory();
                }

                // 5. calculate item count
                $ttContentList = $this->ttContentRepository->findBodyTextElementsByPage($page, $currentSysLanguageUid, $this->settings);

                // set initial some value as fallback if $this->settings['itemsPerHundredSigns']) is not set
                if (floatval($this->settings['itemsPerHundredSigns'])) {

                    $fullTextLength = 0;
                    /** @var \RKW\RkwRelated\Domain\Model\TtContent $ttContentElement */
                    foreach ($ttContentList as $ttContentElement) {
                        $fullTextLength += strlen(strip_tags($ttContentElement->getBodytext()));
                    }

                    $this->limit = intval(floor($fullTextLength / 100 * floatval($this->settings['itemsPerHundredSigns'])));

                    if (($this->limit < intval($this->settings['minItems'])) && (intval($this->settings['minItems']))) {
                        $this->limit = intval($this->settings['minItems']);
                    }
                }

                // 6. Get other pages with same categories (in projects OR page properties ("Seiteneigenschaften"))
                // Only pages of a certain root PID
                $pi = $this->objectManager->get('TYPO3\CMS\Frontend\Plugin\AbstractPlugin');
                $pi->cObj = $this->configurationManager->getContentObject();
                $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pi->pi_getPidList($rootPid, 9999));
                if ($this->settings['pidList']) {
                    $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['pidList']);
                    if ($this->settings['pidListRecursive']) {
                        $pidList = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $pi->pi_getPidList($this->settings['pidList'], 9999));
                    }
                }

                // 6.1 Check for sysCategories or project
                // if there are no sysCategories we check for pages that belong to the same project
                if (count($this->sysCategories)) {

                    /** @toDo: if language is not the standard one (= not "0"), use pagesLanguageOverlayRepository -- really needed? */
                    $this->relatedPages = $this->pagesRepository->findBySysCategory($this->settings, $page, $this->sysCategories, $excludePages, $pidList, $pageNumber, $this->limit);

                } else {
                    if ($this->project) {
                        $this->relatedPages = $this->pagesRepository->findByProject($this->settings, $page, $this->project, $excludePages, $pidList, $pageNumber, $this->limit);
                    } else {
                        // 6.2 General fallback, if either no project and no sysCategories could found
                        $this->department = $page->getTxRkwbasicsDepartment();

                        if (
                            $page->getUid() > 1
                            && !$this->department
                        ) {
                            // Go recursive through the tree and search for a department assignment
                            /** @var \RKW\RkwRelated\Domain\Model\Pages $currentPage */
                            $currentPage = $page;
                            $runs = 0;
                            do {
                                // 1. Get parent
                                $currentParent = $this->pagesRepository->findByIdentifier($currentPage->getPid());
                                if (!$currentParent) {
                                    break;
                                    //===
                                }

                                // 2. Get project of parent, if set
                                if ($currentParent->getTxRkwbasicsDepartment()) {
                                    $this->department = $currentParent->getTxRkwbasicsDepartment();
                                }

                                // 3. Prepare for next round: Climb up to parent page
                                $currentPage = $currentParent;

                            } while (
                                $currentParent->getUid() > 1
                                && !$this->department
                                && $runs < 100
                            );
                        }

                        if ($this->department) {
                            $this->relatedPages = $this->pagesRepository->findByDepartment($this->settings, $page, $this->department, $excludePages, $pidList, $pageNumber, $this->limit);
                        } else {
                            // Log it, if we can't found related pages for page with this uid
                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Cannot find some related pages for page with UID "%s". There are neither sysCategories, nor a project or a department for building potential relations.', $page->getUid()));
                        }
                    }
                }

                // 7. Cache it!
                $cacheTtl = $this->settings['cache']['ttl'] ? $this->settings['cache']['ttl'] : 86400;
                $this->cacheManager->set(
                    $cacheIdentifier,
                    $this->relatedPages,
                    array(
                        'tx_rkwrelated',
                        'tx_rkwrelated_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_similar',
                        'tx_rkwrelated_similar_' . intval($GLOBALS['TSFE']->id),
                    ),
                    $cacheTtl
                );
                $this->cacheManager->set(
                    $cacheIdentifier . '_limit',
                    $this->limit,
                    array(
                        'tx_rkwrelated',
                        'tx_rkwrelated_' . intval($GLOBALS['TSFE']->id),
                        'tx_rkwrelated_similar',
                        'tx_rkwrelated_similar_' . intval($GLOBALS['TSFE']->id),
                    ),
                    $cacheTtl
                );

            }

            if (
                $this->relatedPages === false
                || !count($this->relatedPages)
            ) {
                // if value is "false"
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, sprintf('No results are delivered to the frontend!'));
            } else {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::INFO, sprintf('In total %s results are delivered to the frontend.', count($this->relatedPages)));
            }

            // 8. Choose kind of view. Either normal templating, or its ajax-more functionality
            // Use normal view on pageNumber == 1
            // Else use ajax api for more items
            if ($pageNumber == 1 || $isAjaxCall == false) {
                $this->view->assign('relatedPagesList', $this->relatedPages);
                $this->view->assign('pageNumber', $pageNumber);
                $this->view->assign('currentPluginName', $this->request->getPluginName());
                $this->view->assign('currentPluginNameStrtolower', strtolower($this->request->getPluginName()));
                $this->view->assign('pageTypeAjax', intval($this->settings['pageTypeAjaxSimilarcontent']));
                // do not load float value in view -> this can produce ajax issues. Or write a ViewHelper ;-)
                $this->view->assign('itemsPerHundredSigns', floatval($this->settings['itemsPerHundredSigns']));
                $this->view->assign('limit', $this->limit);
                // do not access settings in view the normal way -> this would produce ajax issues
                $this->view->assign('settingsArray', $this->settings);
                $this->view->assign('ttContentUid', $ttContentUid);

            } else {

                // get JSON helper
                /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
                $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');

                // get new list
                $replacements = array(
                    'pageNumber'                  => $pageNumber,
                    'currentPluginName'           => $this->request->getPluginName(),
                    'currentPluginNameStrtolower' => strtolower($this->request->getPluginName()),
                    'pageTypeAjax'                => intval($this->settings['pageTypeAjaxSimilarcontent']),
                    'itemsPerHundredSigns'        => floatval($this->settings['itemsPerHundredSigns']),
                    'relatedPagesList'            => $this->relatedPages,
                    'limit'                       => $this->limit,
                    'settingsArray'               => $this->settings,
                );

                $jsonHelper->setHtml(
                    'tx-rkwrelated-result-section-' . strtolower($this->request->getPluginName()),
                    $replacements,
                    'append',
                    'Ajax/List/Similar.html'
                );

                print (string)$jsonHelper;
                exit();
                //===
            }
        }


        if ($isAjaxCall) {

            // get JSON helper
            /** @var \RKW\RkwBasics\Helper\Json $jsonHelper */
            $jsonHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\\RkwBasics\\Helper\\Json');
            print (string)$jsonHelper;

            exit();
            //===
        }
    }
}