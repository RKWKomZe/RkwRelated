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
 * Class FileReference
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{

    /**
     * @var string
     */
    protected string $fieldname = '';


    /**
     * @var \RKW\RkwRelated\Domain\Model\File|null
     */
    protected ?File $file = null;


    /**
     * Returns the fieldname
     *
     * @return string $fieldname
     */
    public function getFieldname(): string
    {
        return $this->fieldname;
    }


    /**
     * Sets the fieldname
     *
     * @param string $fieldname
     * @return void
     */
    public function setFieldname(string $fieldname): void
    {
        $this->fieldname = $fieldname;
    }


    /**
     * Returns the uidLocal
     *
     * @return int $uidLocal
     */
    public function getUidLocal(): int
    {
        return $this->uidLocal;
    }


    /**
     * Sets the uidLocal
     *
     * @param int $uidLocal
     * @return void
     */
    public function setUidLocal(int $uidLocal): void
    {
        $this->uidLocal = $uidLocal;
    }


    /**
     * Set file
     *
     * @param \RKW\RkwRelated\Domain\Model\File $file
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }


    /**
     * Get file
     *
     * @return \RKW\RkwRelated\Domain\Model\File
     */
    public function getFile():?File
    {
        return $this->file;
    }
}
