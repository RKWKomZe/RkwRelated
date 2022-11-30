<?php

namespace RKW\RkwRelated\Domain\Repository;
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
 * TtContentRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TtContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function initializeObject()
    {
        $this->defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setRespectSysLanguage(false);
    }

    /**
     * Get main bodytext element of page
     *
     * @param \RKW\RkwRelated\Domain\Model\Pages $page
     * @param integer $sysLanguageUid
     * @param array $settings
     * @return NULL|object
     */
    public function findBodyTextElementsByPage($page, $sysLanguageUid, $settings)
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalAnd(
                $query->equals('pid', $page),
                $query->equals('sysLanguageUid', $sysLanguageUid),
                $query->equals('colpos', $settings['colPosOfPagesMainContentElement']),
                $query->logicalOr(
                    $query->equals('ctype', 'text'),
                    $query->equals('ctype', 'textpic')
                )
            )
        );

        return $query->execute();
        //====
    }


    /*
     * findFlexformDataByUid
     *
     * @param integer $ttContentUid
     * @param string $pluginName
     * @return array
     */
    public function findFlexformDataByUid($ttContentUid, $pluginName)
    {
        $query = $this->createQuery();
        $query->statement('SELECT pi_flexform from tt_content where list_type="rkwrelated_' . strtolower($pluginName) . '" and uid = ' . $ttContentUid);
        $ttContent = $query->execute(true);
        $flexformData = array();
        if (is_array($ttContent)) {

            $xml = simplexml_load_string($ttContent[0]['pi_flexform']);
            $flexformData['uid'] = $ttContentUid;

            if (
                (isset($xml))
                && (isset($xml->data))
                && (is_object($xml->data->sheet))
            ) {
                foreach ($xml->data->sheet as $sheet) {
                    foreach ($sheet->language->field as $field) {
                        $flexformData[str_replace('settings.flexform.', '', (string)$field->attributes())] = (string)$field->value;
                    }
                }
            }
        }

        return $flexformData;
        //===
    }
}
