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

use RKW\RkwRelated\Domain\Model\Pages;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setRespectSysLanguage(false);
    }


    /**
     * Get main bodytext element of page
     *
     * @param \RKW\RkwRelated\Domain\Model\Pages $page
     * @param int $sysLanguageUid
     * @param array $settings
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findBodyTextElementsByPage(Pages $page, int $sysLanguageUid, array $settings): QueryResultInterface
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
    }


    /**
     * findFlexformDataByUid
     *
     * @param int $ttContentUid
     * @param string $pluginName
     * @return array
     */
    public function findFlexformDataByUid(int $ttContentUid, string $pluginName): array
    {
        $query = $this->createQuery();
        $query->statement('SELECT pi_flexform from tt_content where list_type="rkwrelated_' .
            strtolower($pluginName) . '" and uid = ' . $ttContentUid);

        $ttContent = $query->execute(true);
        $flexformData =[];
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
                        $flexformData[
                            str_replace(
                            'settings.flexform.',
                            '',
                            (string)$field->attributes())
                        ] = (string)$field->value;
                    }
                }
            }
        }

        return $flexformData;
    }
}
