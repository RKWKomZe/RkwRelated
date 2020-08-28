<?php

namespace RKW\RkwRelated\ViewHelpers;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ShowMoreItems
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated This class is deprecated and will be removed soon.
 */
class ShowMoreItemsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * More Results available?
     *
     * @param float $itemsPerHundredSigns
     * @param integer $pageNumber
     * @param integer $itemsTotal
     * @return boolean
     */
    public function render($itemsPerHundredSigns = 0.0, $pageNumber = 1, $itemsTotal = 1)
    {
        GeneralUtility::logDeprecatedFunction();

        $pageNumber--;

        if ($pageNumber * $itemsPerHundredSigns < $itemsTotal) {
            return true;
            //===
        }

        return false;
        //===
    }

}