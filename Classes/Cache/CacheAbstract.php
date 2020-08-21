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

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Class CacheAbstract
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRelated
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class CacheAbstract implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var string Key for cache
     */
    protected $_key = 'rkwrelated';

    /**
     * @var string Identifier for cache
     */
    protected $_identifier = 'rkwrelated';

    /**
     * @var string Contains context mode (Production, Development...)
     */
    protected $contextMode;

    /**
     * @var string Contains environment mode (FE or BE)
     */
    protected $environmentMode;

    /**
     * @var array Contains relevant tags
     */
    protected $tags = [];


    /**
     * Returns cache identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }


    /**
     * Sets cache identifier
     *
     * @param string $plugin
     * @param int $contentId
     * @param int $page
     * @return $this
     */
    public function setIdentifier($plugin, $contentId, $page)
    {
        $pid = intval($GLOBALS['TSFE']->id);
        $languageUid = intval($GLOBALS['TSFE']->config['config']['sys_language_uid']);

        $this->_identifier = $this->_key . '_' . strtolower($plugin) . '_' . $pid . '_' . $contentId . '_' . $languageUid . '_' . intval($page);
        $this->setTags($plugin);

        return $this;
    }

    /**
     * Returns cached object
     *
     * @return bool
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function hasContent()
    {

        // only use cache when in production
        // and when called from FE
        if (
            ($this->getContextMode() != 'Production')
            || ($this->getEnvironmentMode() != 'FE')
        ) {
           // return false;
        }

        return $this->getCache()
            ->has($this->getIdentifier());
    }


    /**
     * Returns cached object
     *
     * @return mixed
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function getContent()
    {

        // only use cache when in production
        // and when called from FE
        if (
            ($this->getContextMode() != 'Production')
            || ($this->getEnvironmentMode() != 'FE')
        ) {
            //return false;
        }

        return $this->getCache()
            ->get($this->getIdentifier());
    }


    /**
     * sets cached content
     *
     * @param mixed $data
     * @param integer $lifetime
     * @param array $tags
     * @return $this
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function setContent($data, $lifetime = 21600, array $tags = [])
    {

        // only use cache when in production
        // and when called from FE
        if (
            ($this->getContextMode() != 'Production')
            || ($this->getEnvironmentMode() != 'FE')
        ) {
           // return $this;
        }

        $this->getCache()
            ->set(
                $this->getIdentifier(),
                $data,
                ($tags ? $tags : $this->tags),
                $lifetime
            );

        return $this;
    }



    /**
     * Sets the relevant tags
     *
     * @param string $plugin
     * @return $this
     */
    public function setTags($plugin)
    {
        $pid = intval($GLOBALS['TSFE']->id);
        $this->tags = [
            'tx_rkwrelated',
            'tx_rkwrelated_' . $pid,
            'tx_rkwrelated_more',
            'tx_rkwrelated_more_' . $pid,
            'tx_rkwrelated_more_' . strtolower($plugin),
            'tx_rkwrelated_more_' . strtolower($plugin) . '_' . $pid,
        ];

        return $this;
    }




    /**
     * Returns cache
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function getCache()
    {
        /** @var $cacheManager \TYPO3\CMS\Core\Cache\CacheManager */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CacheManager::class);
        return $cacheManager->getCache($this->_key);
    }


    /**
     * Function to return the current TYPO3_CONTEXT.
     *
     * @return string|NULL
     */
    protected function getContextMode()
    {

        if (!$this->contextMode) {
            if (getenv('TYPO3_CONTEXT')) {
                $this->contextMode = getenv('TYPO3_CONTEXT');
            }
        }

        return $this->contextMode;
    }


    /**
     * Function to return the current TYPO3_MODE.
     * This function can be mocked in unit tests to be able to test frontend behaviour.
     *
     * @return string
     * @see \TYPO3\CMS\Core\Resource\AbstractRepository
     */
    protected function getEnvironmentMode()
    {

        if (!$this->environmentMode) {
            if (TYPO3_MODE) {
                $this->environmentMode = TYPO3_MODE;
            }
        }

        return $this->environmentMode;
    }


    /**
     * Constructor
     *
     * @param string $environmentMode
     * @param string $contextMode
     */
    public function __construct($environmentMode = null, $contextMode = null)
    {

        if ($environmentMode) {
            $this->environmentMode = $environmentMode;
        }

        if ($contextMode) {
            $this->contextMode = $contextMode;
        }
    }

}