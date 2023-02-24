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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * PreselectViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PreselectViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('filterList', 'array', 'An array of filter set.');
        $this->registerArgument('value', 'scalar', 'The value to compare the filters with.');
    }


    /**
     * @param array  $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return bool|int
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        // compare mode
        if (
            ($value = $arguments['value'])
            && (is_scalar($value))
        ){

            if (
                ($filterList = $arguments['filterList'])
                && (is_array($filterList))
                && (isset($filterList[$value]))
            ){
                return true;
            }

            return false;
        }


        // numeric mode
        if (
            ($filterList = $arguments['filterList'])
            && (is_array($filterList))
            && (count($filterList) == 1)  // select only ONE - or nothing
        ){
            return $filterList[0];
        }

        return 0;
    }

}
