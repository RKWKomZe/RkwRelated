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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use RKW\RkwProjects\Domain\Repository\ProjectsRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class MoreController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MoreController extends AbstractController
{

    /**
     * @var int
     */
    protected int $year = 2011;


    /**
     * @return void
     */
    public function initializeAction(): void
    {
        // optional overwrite of year by TypoScript (used as filter option in template)
        $this->year = $this->settings['staticInitialYearForFilter'] ? intval($this->settings['staticInitialYearForFilter']) : $this->year;
    }


    /**
     * listAction
     *
     * @param array $filter
     * @param int $pageNumber
     * @param int $ttContentUid
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @todo some users disable caching for no reason. So now we have performance issues
     */
    public function listAction(array $filter = array(), int $pageNumber = 0, int $ttContentUid = 0): void
    {

        // get plugins content element UID
        // Attention: Following line doesn't work in ajax-context (return PID instead of plugins content element uid)
        if (!$ttContentUid) {
            $ttContentUid = $this->ajaxHelper->getContentUid();
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
            'categories' => $this->filterUtility::getCombinedFilterByName(
                'categories',
                $this->settings,
                $filter
            ),
            'year' => intval($filter['year'])
        ];

        //  Set cache-identifiers
        /** @var \RKW\RkwRelated\Cache\ContentCache $contentCache */
        $contentCache = $this->getCache();
        $contentCache->setEntryIdentifier(
            $contentCache->generateEntryIdentifier(
                $ttContentUid,
                $pageNumber,
                array_merge($this->settings, $filter)
            )
        );

        /** @var \RKW\RkwRelated\Cache\CountCache $countCache */
        $countCache = $this->getCache(true);
        $countCache->setEntryIdentifier(
            $contentCache->generateEntryIdentifier(
                $ttContentUid,
                $pageNumber,
                array_merge($this->settings, $filter)
            )
        );

        // Current state: No caching if someone is filtering via frontend form
        if (
           ($contentCache->hasContent())
            && ($countCache->hasContent())
            && (!$filter)
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

            // determine items per page
            $itemsPerPage = 10;

            if (is_array($this->settings['itemLimitPerPage'])) {

                $layout = strtolower($this->settings['layout'] ?: 'default');
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

            // Include & Exclude pages
            $excludePidList = $this->filterUtility::getExcludePidList($this->settings);
            $includePidList = $this->filterUtility::getIncludePidList($this->settings);

            // settings for publications including fallback to old solution
            $findPublications = intval($this->settings['displayPublications']);

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

        $showMoreLink = ($nextRelatedPagesCount < 1) ? false : !boolval($this->settings['hideMoreLink']);

        /** @deprecated */
        $this->settings['maximumShownResults'] = PHP_INT_MAX;
        $this->settings['showMoreLink'] = $showMoreLink;

        // to avoid dependence to RkwProject, we're calling the repository this way
        $projectList = null;
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')) {
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
            /** @var \RKW\RkwProjects\Domain\Repository\ProjectsRepository $projectsRepository */
            $projectsRepository = $objectManager->get(ProjectsRepository::class);
            $projectList = $projectsRepository->findAllByVisibility();
        }

        // Get CategoryList for Filter
        $categoryList = [];

        // -> Hint: We only want that list, if there is a "SysCategory parent UID" is defined via TSCONFIG
        $pageTsConfig = GeneralUtility::removeDotsFromTS(BackendUtility::getPagesTSconfig(intval($GLOBALS['TSFE']->id)));
        // check either if path exists AND if there is a value!
        if (
            isset($pageTsConfig['TCEFORM']['tt_content']['pi_flexform']['rkwrelated_morecontent']['filteroptions']['settings.categoriesList']['config']['treeConfig']['rootUid'])
            && $pageTsConfig['TCEFORM']['tt_content']['pi_flexform']['rkwrelated_morecontent']['filteroptions']['settings.categoriesList']['config']['treeConfig']['rootUid']
        ) {
            $categoryParentRootUid = $pageTsConfig['TCEFORM']['tt_content']['pi_flexform']['rkwrelated_morecontent']['filteroptions']['settings.categoriesList']['config']['treeConfig']['rootUid'];
            if ($categoryParentRootUid) {
                $categoryList = $this->categoryRepository->findByParentRecursive([$categoryParentRootUid]);
            }
        }

        $years = array_combine(
            range(date("Y"), $this->year),
            range(date("Y"), $this->year)
        );

        $assignments = [
            'layout'                 => ($this->settings['layout'] ? $this->settings['layout'] : 'Default'),
            'relatedPagesList'       => $relatedPages,
            'pageNumber'             => $pageNumber,
            'showMoreLink'           => $showMoreLink,
            'currentPluginName'      => $this->request->getPluginName(),
            'departmentList'         => $this->departmentRepository->findAllByVisibility(),
            'categoryList'           => $categoryList,
            'documentTypeList'       => $this->documentTypeRepository->findAllByTypeAndVisibility('publications'),
            'projectList'            => $projectList,
            'years'                  => $years,
            'filter'                 => $filter,
            'filterFull'             => $filterList,
            'sysLanguageUid'         => intval($GLOBALS['TSFE']->config['config']['sys_language_uid']),
            'linkInSameWindow'       => ($this->settings['openLinksInSameWindowOverride'] ?? $this->settings['openLinksInSameWindow'])
        ];

        $this->view->assignMultiple($assignments);

    }

}
