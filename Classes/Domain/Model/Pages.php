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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Pages
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Pages extends PagesAbstract
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>|null
     */
    protected ?ObjectStorage $categories;


    /**
     * @var int
     */
    protected int $txRkwpdf2contentIsImport = 0;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>|null
     */
    protected ?ObjectStorage $txRkwauthorsAuthorship = null;



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
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->txRkwauthorsAuthorship = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

    }


    /**
     * Adds a SysCategory
     *
     * @param \RKW\RkwRelated\Domain\Model\SysCategory $sysCategory
     * @return void
     */
    public function addSysCategory(SysCategory $sysCategory): void
    {
        $this->categories->attach($sysCategory);
    }


    /**
     * Removes a SysCategory
     *
     * @param \RKW\RkwRelated\Domain\Model\SysCategory $sysCategory
     * @return void
     */
    public function removeSysCategory(SysCategory $sysCategory): void
    {
        $this->categories->detach($sysCategory);
    }


    /**
     * Returns the sysCategory
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory>
     */
    public function getSysCategory(): ObjectStorage
    {
        return $this->categories;
    }


    /**
     * Sets the sysCategory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRelated\Domain\Model\SysCategory> $sysCategory
     * @return void
     */
    public function setSysCategory(ObjectStorage $sysCategory): void
    {
        $this->categories = $sysCategory;
    }


    /**
     * Returns the txRkwpdf2contentIsImport
     *
     * @return int txRkwpdf2contentIsImport
     */
    public function getTxRkwpdf2contentIsImport(): int
    {
        return $this->txRkwpdf2contentIsImport;
    }


    /**
     * Returns the txRkwauthorsAuthorship
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwAuthors\Domain\Model\Authors>
     */
    public function getTxRkwauthorsAuthorship(): ObjectStorage
    {
        return $this->txRkwauthorsAuthorship;
    }


}
