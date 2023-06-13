<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "rkw_related"
 *
 * Auto generated by Extension Builder 2017-04-04
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'RKW Related',
	'description' => 'Provides plugins for related content',
	'category' => 'plugin',
    'author' => 'Maximilian Fäßler, Steffen Kroggel',
    'author_email' => 'maximilian@faesslerweb.de, developer@steffenkroggel.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '9.5.7',
	'constraints' => [
		'depends' => [
            'typo3' => '9.5.0-9.5.99',
            'accelerator' => '9.5.2-9.5.99',
            'ajax_api' => '9.5.0-9.5.99',
            'core_extended' => '9.5.4-9.5.99',
            'rkw_basics' => '9.5.0-9.5.99',
            'rkw_projects' => '9.5.0-9.5.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
