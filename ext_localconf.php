<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'RKW.' . $_EXTKEY,
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
	'RKW.' . $_EXTKEY,
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
	'RKW.' . $_EXTKEY,
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
	'RKW.' . $_EXTKEY,
	'Morecontentpublication',
	array(
		'More' => 'list',

	),
	// non-cacheable actions
	array(
		'More' => 'list',

	)
);

// register the hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$_EXTKEY] = 'RKW\\RkwRelated\\Hooks\\RelatedHook';

// caching
if( !is_array($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY] ) ) {
	$GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY] = array();
}
// Hier ist der entscheidende Punkt! Es ist der Cache von Variablen gesetzt!
if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['frontend'] ) ) {
	$GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend';
}

if( !isset($GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['groups'] ) ) {
	$GLOBALS['TYPO3_CONF_VARS'] ['SYS']['caching']['cacheConfigurations'][$_EXTKEY]['groups'] = array('pages');
}

// set logger
$GLOBALS['TYPO3_CONF_VARS']['LOG']['RKW']['RkwRelated']['writerConfiguration'] = array(

    // configuration for WARNING severity, including all
    // levels with higher severity (ERROR, CRITICAL, EMERGENCY)
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => array(
        // add a FileWriter
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
            // configuration for the writer
            'logFile' => 'typo3temp/logs/tx_rkwrelated.log'
        )
    ),
);


// Signal Slot for varnish-extension
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('varnish')) {
    
    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
     */
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
    $signalSlotDispatcher->connect(
        'RKW\\RkwRelated\\Hooks\\TtContentHook',
        \RKW\RkwRelated\Hooks\RelatedHook::SIGNAL_CLEAR_PAGE_VARNISH,
        'RKW\\RkwRelated\\Service\\VarnishService',
        'clearCacheOfPageEvent'
    );
}
