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
 * Class SysCategory
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SysCategory extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * @var \RKW\RkwRelated\Domain\Model\Pages|null
     */
    protected ?Pages $txRkwrelatedLink = null;


    /**
     * Get the txRkwrelatedLink
     *
     * @return \RKW\RkwRelated\Domain\Model\Pages|null
     */
    public function getTxRkwrelatedLink():? Pages
    {
        return $this->txRkwrelatedLink;
    }

    /**
     * Set the txRkwrelatedLink
     *
     * @param \RKW\RkwRelated\Domain\Model\Pages $txRkwrelatedLink
     * @return void
     */
    public function setTxRkwrelatedLink(Pages $txRkwrelatedLink): void
    {
        $this->txRkwrelatedLink = $txRkwrelatedLink;
    }

}
