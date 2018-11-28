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
class Pages extends \RKW\RkwProjects\Domain\Model\Pages
{
    /**
     * description
     *
     * @var string
     */
    protected $description;

    /**
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>
     */
    protected $categories;

    /**
     * txBmpdf2contentIsImport
     *
     * @var integer
     */
    protected $txBmpdf2contentIsImport;

    /**
     * lastUpdated
     *
     * @var integer
     */
    protected $lastUpdated;

    /**
     * txRkwsearchPubdate
     *
     * @var integer
     */
    protected $txRkwsearchPubdate;

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();
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
    }


    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * Returns the txBmpdf2contentIsImport
     *
     * @return integer txBmpdf2contentIsImport
     */
    public function getBmpdf2contentIsImport()
    {
        return $this->txBmpdf2contentIsImport;
    }

    /**
     * Sets the txBmpdf2contentIsImport
     *
     * @param integer $txBmpdf2contentIsImport
     */
    public function setTxBmpdf2contentIsImport($txBmpdf2contentIsImport)
    {
        $this->txBmpdf2contentIsImport = $txBmpdf2contentIsImport;
    }

    /**
     * Returns the lastUpdated
     *
     * @return integer lastUpdated
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Sets the lastUpdated
     *
     * @param integer $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * Returns the txRkwsearchPubdate
     *
     * @return integer
     */
    public function getTxRkwsearchPubdate()
    {
        return $this->txRkwsearchPubdate;
    }

    /**
     * Sets the txRkwsearchPubdate
     *
     * @param integer $txRkwsearchPubdate
     * @return void
     */
    public function setTxRkwsearchPubdate($txRkwsearchPubdate)
    {
        $this->txRkwsearchPubdate = $txRkwsearchPubdate;
    }
}