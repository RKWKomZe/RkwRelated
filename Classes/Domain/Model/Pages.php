<?php

namespace RKW\RkwRelated\Domain\Model;
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
 * Pages
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Pages extends PagesAbstract
{

    /**
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>
     */
    protected $categories;

    /**
     * txRkwpdf2contentIsImport
     *
     * @var \integer
     */
    protected $txRkwpdf2contentIsImport;

    /**
     * txRkwsearchPubdate
     *
     * @var integer
     * @deprecated
     */
    protected $txRkwsearchPubdate;

    /**
     * txRkwauthorsAuthorship
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>
     */
    protected $txRkwauthorsAuthorship = null;


    /**
     * __construct
     */
    public function __construct()
    {

        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }


    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->sysCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txRkwauthorsAuthorship = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Adds a SysCategory
     *
     * @param \RKW\RkwRelated\Domain\Model\SysCategory $sysCategory
     */
    public function addSysCategory(\RKW\RkwRelated\Domain\Model\SysCategory $sysCategory)
    {
        $this->categories->attach($sysCategory);
    }

    /**
     * Removes a SysCategory
     *
     * @param \RKW\RkwRelated\Domain\Model\SysCategory $sysCategory
     */
    public function removeSysCategory(\RKW\RkwRelated\Domain\Model\SysCategory $sysCategory)
    {
        $this->categories->detach($sysCategory);
    }

    /**
     * Returns the sysCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory> sysCategory
     */
    public function getSysCategory()
    {
        return $this->categories;
    }

    /**
     * Sets the sysCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory> $sysCategory
     */
    public function setSysCategory(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $sysCategory)
    {
        $this->categories = $sysCategory;
    }


    /**
     * Returns the txRkwpdf2contentIsImport
     *
     * @return \string txRkwpdf2contentIsImport
     */
    public function getTxRkwpdf2contentIsImport() {
        return $this->txRkwpdf2contentIsImport;
    }



    /**
     * Returns the txRkwsearchPubdate
     *
     * @return integer
     * @deprecated
     */
    public function getTxRkwsearchPubdate()
    {
        return $this->txRkwsearchPubdate;
    }



    /**
     * Returns the txRkwauthorsAuthorship
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors> $txRkwauthorsAuthorship
     */
    public function getTxRkwauthorsAuthorship()
    {
        return $this->txRkwauthorsAuthorship;
    }


}