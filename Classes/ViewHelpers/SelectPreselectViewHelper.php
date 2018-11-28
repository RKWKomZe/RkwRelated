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

/**
 * SelectPreselectViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SelectPreselectViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param integer $filterUid
     * @param string $configList
     * @return integer
     */
    public function render($filterUid, $configList)
    {
        $configList = explode(',', $configList);

        // is a filter set?
        if (intval($filterUid)) {
            return intval($filterUid);
            //===
        }

        // If only one item in the configList is set, we preselect this
        if (count($configList) == 1) {
            return $configList[0];
            //===
        }

        return 0;
        //===
    }

}