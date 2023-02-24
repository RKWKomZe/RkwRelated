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
use RKW\RkwRelated\Domain\Repository\PagesLanguageOverlayRepository;
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
    protected PagesRepository $pagesRepository;


    /**
     * @var \RKW\RkwRelated\Domain\Repository\PagesLanguageOverlayRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected PagesLanguageOverlayRepository $pagesLanguageOverlayRepository;


    /**
     * @var \RKW\RkwRelated\Domain\Repository\TtContentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected TtContentRepository $ttContentRepository;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\DepartmentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected DepartmentRepository $departmentRepository;


    /**
     * @var \RKW\RkwBasics\Domain\Repository\DocumentTypeRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected DocumentTypeRepository $documentTypeRepository;


    /**
     * @var \RKW\RkwRelated\Domain\Repository\SysCategoryRepository|null
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?SysCategoryRepository $categoryRepository = null;


    /**
     * @var \RKW\RkwRelated\Utilities\FilterUtility
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected FilterUtility $filterUtility;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger;


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
        $identifier = $this->extensionName . 'Content';
        if ($countCache) {
            $class = CountCache::class;
            $identifier = $this->extensionName . 'Count';
        }

        /** @var \RKW\RkwRelated\Cache\CacheInterface $cache */
        $cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($class);
        $cache->setIdentifier($identifier)
            ->setRequest($this->request);

        return $cache;
    }

}

