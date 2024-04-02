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

use RKW\RkwBasics\Domain\Repository\DepartmentRepository;
use RKW\RkwBasics\Domain\Repository\DocumentTypeRepository;
use RKW\RkwRelated\Cache\CacheInterface;
use RKW\RkwRelated\Cache\ContentCache;
use RKW\RkwRelated\Cache\CountCache;
use RKW\RkwRelated\Domain\Repository\PagesRepository;
use RKW\RkwRelated\Domain\Repository\SysCategoryRepository;
use RKW\RkwRelated\Domain\Repository\TtContentRepository;
use RKW\RkwRelated\Utilities\FilterUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractController extends \Madj2k\AjaxApi\Controller\AjaxAbstractController
{

    /**
     * @var \RKW\RkwRelated\Domain\Repository\PagesRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?PagesRepository $pagesRepository = null;


    /**
     * @var \RKW\RkwRelated\Domain\Repository\TtContentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?TtContentRepository $ttContentRepository = null;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\DepartmentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?DepartmentRepository $departmentRepository = null;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\DocumentTypeRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?DocumentTypeRepository $documentTypeRepository = null;


    /**
     * @var \RKW\RkwRelated\Domain\Repository\SysCategoryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?SysCategoryRepository $categoryRepository = null;


    /**
     * @var \RKW\RkwRelated\Utilities\FilterUtility
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?FilterUtility $filterUtility = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * @param \RKW\RkwRelated\Domain\Repository\PagesRepository $pagesRepository
     */
    public function injectPagesRepository(PagesRepository $pagesRepository)
    {
        $this->pagesRepository = $pagesRepository;
    }


    /**
     * @param \RKW\RkwRelated\Domain\Repository\TtContentRepository $ttContentRepository
     */
    public function injectTtContentRepository(TtContentRepository $ttContentRepository)
    {
        $this->ttContentRepository = $ttContentRepository;
    }


    /**
     * @param \RKW\RkwBasics\Domain\Repository\DepartmentRepository $departmentRepository
     */
    public function injectDepartmentRepository(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }


    /**
     * @param \RKW\RkwBasics\Domain\Repository\DocumentTypeRepository $documentTypeRepository
     */
    public function injectDocumentTypeRepository(DocumentTypeRepository $documentTypeRepository)
    {
        $this->documentTypeRepository = $documentTypeRepository;
    }


    /**
     * @param \RKW\RkwRelated\Domain\Repository\SysCategoryRepository $categoryRepository
     */
    public function injectSysCategoryRepository(SysCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    /**
     * @param \RKW\RkwRelated\Utilities\FilterUtility $filterUtility
     */
    public function injectFilterUtility(FilterUtility $filterUtility)
    {
        $this->filterUtility = $filterUtility;
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


    /**
     * Returns the cache object
     *
     * @param bool $countCache
     * @return \RKW\RkwRelated\Cache\CacheInterface
     */
    protected function getCache(bool $countCache = false): CacheInterface
    {
        $class = ContentCache::class;
        $identifier = 'RkwRelatedContent';
        if ($countCache) {
            $class = CountCache::class;
            $identifier = 'RkwRelatedCount';
        }

        /** @var \RKW\RkwRelated\Cache\CacheInterface $cache */
        $cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class);
        $cache->setIdentifier($identifier)
            ->setRequest($this->request);

        return $cache;
    }

}

