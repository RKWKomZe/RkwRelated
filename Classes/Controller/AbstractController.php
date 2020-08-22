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
 * Class AbstractController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractController extends \RKW\RkwAjax\Controller\AjaxAbstractController
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
     * filterUtility
     *
     * @var \RKW\RkwRelated\Utilities\FilterUtility
     * @inject
     */
    protected $filterUtility;


    /**
     * @var \RKW\RkwRelated\Cache\ContentCache
     * @inject
     */
    protected $contentCache;


    /**
     * @var \RKW\RkwRelated\Cache\CountCache
     * @inject
     */
    protected $countCache;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Checks if the current call is based on ajax or not
     *
     * @return bool
     * @deprecated
     */
    protected function isAjaxCall()
    {
        if (
            (
                ($this->settings['pageTypeAjaxMoreContent'])
                && (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContent']))
            )
            || (
                ($this->settings['pageTypeAjaxMoreContent2'])
                && (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContent2']))
            )
            || (
                ($this->settings['pageTypeAjaxMoreContentPublication'])
                && (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxMoreContentPublication']))
            )
            || (
                ($this->settings['pageTypeAjaxSimilarcontent'])
                && (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('type') == intval($this->settings['pageTypeAjaxSimilarcontent']))
            )
        ) {
            return true;
        }

        return false;
    }


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
    }

}

