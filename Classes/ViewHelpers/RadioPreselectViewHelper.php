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
 * RadioPreselectViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated This class is deprecated and will be removed soon. Use RKW\RkwRelated\ViewHelpers\PreselectViewHelper instead.
 */
class RadioPreselectViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param integer $currentUid
     * @param integer $filterUid
     * @param string $configList
     * @return boolean
     */
    public function render($currentUid, $filterUid, $configList)
    {

        GeneralUtility::logDeprecatedFunction();

        $configList = explode(',', $configList);

        // is a filter set?
        if (intval($filterUid) == $currentUid) {
            return true;
        }

        // If only one item in the configList is set, we preselect this
        if (
            (count($configList) == 1)
            && ($configList[0] == $currentUid)
            && (!$filterUid)
        ) {
            return true;
        }

        return false;
    }

}