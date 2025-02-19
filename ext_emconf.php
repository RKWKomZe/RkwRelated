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
	'clearCacheOnLoad' => 0,
	'version' => '10.4.3',
	'constraints' => [
		'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'accelerator' => '10.4.0-12.4.99',
            'ajax_api' => '10.4.0-12.4.99',
            'core_extended' => '10.4.0-12.4.99',
            'rkw_basics' => '10.4.0-12.4.99',
            'rkw_projects' => '10.4.0-12.4.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];
