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

use TYPO3\CMS\Core\Log\LogManager;

/**
 * Class AbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractController extends \RKW\RkwAjax\Controller\AjaxAbstractController
{

    /**
     * pagesRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\PagesRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pagesRepository = null;

    /**
     * pagesLanguageOverlayRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\PagesLanguageOverlayRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $pagesLanguageOverlayRepository = null;

    /**
     * ttContentRepository
     *
     * @var \RKW\RkwRelated\Domain\Repository\TtContentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $ttContentRepository = null;


    /**
     * departmentRepository
     *
     * @var \RKW\RkwBasics\Domain\Repository\DepartmentRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $departmentRepository = null;

    /**
     * documentTypeRepository
     *
     * @var \RKW\RkwBasics\Domain\Repository\DocumentTypeRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $documentTypeRepository = null;


    /**
     * filterUtility
     *
     * @var \RKW\RkwRelated\Utilities\FilterUtility
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $filterUtility;


    /**
     * @var \RKW\RkwRelated\Cache\ContentCache
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $contentCache;


    /**
     * @var \RKW\RkwRelated\Cache\CountCache
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $countCache;


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
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}

