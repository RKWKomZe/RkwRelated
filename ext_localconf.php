<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Configure Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Similarcontent',
            array(
                'Similar' => 'list',

            ),
            // non-cacheable actions
            array(
                'Similar' => 'list',

            )
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Morecontent',
            array(
                'More' => 'list',

            ),
            // non-cacheable actions
            array(
                'More' => 'list',

            )
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Morecontent2',
            array(
                'More' => 'list',

            ),
            // non-cacheable actions
            array(
                'More' => 'list',

            )
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Morecontentpublication',
            array(
                'More' => 'list',

            ),
            // non-cacheable actions
            array(
                'More' => 'list',

            )
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'RKW.' . $extKey,
            'Selectedcategories',
            array(
                'Category' => 'showSelected',

            ),
            // non-cacheable actions
            array(
                'Category' => 'showSelected',

            )
        );


        //=================================================================
        // Register Hooks
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] = 'RKW\\RkwRelated\\Hooks\\RelatedHook';

        //=================================================================
        // Register Signal-Slot for Varnish
        //=================================================================
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('varnish')) {

            /**
             * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
             */
            $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
            $signalSlotDispatcher->connect(
                'RKW\\RkwRelated\\Hooks\\TtContentHook',
                \RKW\RkwRelated\Hooks\RelatedHook::SIGNAL_CLEAR_PAGE_VARNISH,
                'RKW\\RkwRelated\\Service\\VarnishService',
                'clearCacheOfPageEvent'
            );
        }

        //=================================================================
        // Add XClasses for extending existing classes
        // ATTENTION: deactivated due to faulty mapping in TYPO3 9.5
        //=================================================================
        /*
        // for TYPO3 12+
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\RKW\RkwProjects\Domain\Model\Pages::class] = [
            'className' => \RKW\RkwRelated\Domain\Model\Pages::class
        ];

        // for TYPO3 9.5 - 11.5 only, not required for TYPO3 12
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
            ->registerImplementation(
                \RKW\RkwProjects\Domain\Model\Pages::class,
                \RKW\RkwRelated\Domain\Model\Pages::class
            );
        */
        //=================================================================
        // Register Caching
        //=================================================================
        foreach (['content', 'count'] as $cache) {
            $cacheIdentifier = \Madj2k\CoreExtended\Utility\GeneralUtility::underscore($extKey);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier . '_' . $cache] = [
                'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
                'groups' => [
                    'all',
                    'pages',
                ],
            ];
        }
        //=================================================================
        // Register Logger
        //=================================================================
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwRelated']['writerConfiguration'] = array(

            // configuration for WARNING severity, including all
            // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
            \TYPO3\CMS\Core\Log\LogLevel::WARNING => array(
                // add a FileWriter
                'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
                    // configuration for the writer
                    'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath()  . '/log/tx_rkwrelated.log'
                )
            ),
        );
    },
    'rkw_related'
);



