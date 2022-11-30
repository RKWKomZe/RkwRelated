<?php

namespace RKW\RkwRelated\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * PageTranslateProperty
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageTranslatePropertyViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', 'The pageUid to translate.', true);
        $this->registerArgument('dbField', 'string', 'The database field to translate.', true);
        $this->registerArgument('showHiddenTranslations', 'bool', 'Show hidden translations as well (overrides hidden=1).', false, false);
        $this->registerArgument('sysLanguageUid', 'int', 'The sysLanguageUid to use for translation.', false, 0);
    }

    /**
     * Get translation information
     *
     * @return string
     */
    public function render(): string
    {
        $pageUid = $this->arguments['pageUid'];
        $dbField = $this->arguments['dbField'];
        $showHiddenTranslations = $this->arguments['showHiddenTranslation'];
        $sysLanguageUid = $this->arguments['sysLanguageUid'];

        // if there is no sysLanguageUid or simple the standard one (0), do nothing!
        if (!$sysLanguageUid) {
            return '';
        }

        $pageUid = $this->getPageUid($pageUid);
        $sysLanguageUid = intval($sysLanguageUid);

        /**
         * @deprecated
         * @todo rework!
         if ($pageUid) {

            // Get all page language overlay records of the selected page
            $table = 'pages_language_overlay';
            $whereClause = 'pid=' . $pageUid . ' ';
            if ($sysLanguageUid > 0) {
                $whereClause .= 'AND sys_language_uid=' . $sysLanguageUid . ' ';
            }

            $whereClause .= $GLOBALS['TSFE']->sys_page->enableFields($table, $showHiddenTranslations);
            $pageOverlays = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $whereClause);

            $content = '';
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pageOverlays)) {
                $content = $row[strip_tags(GeneralUtility::camelCaseToLowerCaseUnderscored($dbField))];
            }

            if ($pageOverlays) {
                return $content;
            }
        }*/

        return '';
    }


    /**
     * Get page via pageUid argument or current id
     *
     * @param integer $pageUid Uid of the page
     * @return integer
     */
    protected function getPageUid($pageUid = null)
    {

    /**
     * @deprecated
     * @todo rework!
        if ($pageUid === null) {
            $pageUid = (integer)$this->renderChildren();
        }
        if (0 === $pageUid) {
            $pageUid = $GLOBALS['TSFE']->id;
        }

        return (integer)$pageUid;
      **/
    }

}
