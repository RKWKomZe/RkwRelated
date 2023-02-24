<?php
namespace RKW\RkwRelated\Cache;

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
 * Class CacheAbstract
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRelated
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class CacheAbstract extends \Madj2k\Accelerator\Cache\CacheAbstract implements CacheInterface
{

    /**
     * Generate entry identifier
     *
     * @param int $contentId
     * @param int $page
     * @param array $settings
     * @return string
     */
    public function generateEntryIdentifier(int $contentId, int $page, array $settings = []): string
    {
        $pid = intval($GLOBALS['TSFE']->id);
        $languageUid = intval($GLOBALS['TSFE']->config['config']['sys_language_uid']);
        $settingsMd5 = md5(serialize($settings));

       return $pid . '_' . $contentId . '_' . $languageUid . '_' . $page . '_' . $settingsMd5;
    }


}
