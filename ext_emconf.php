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
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '8.7.9',
	'constraints' => [
		'depends' => [
            'typo3' => '7.6.0-7.6.99',
            'rkw_basics' => '8.7.0-8.7.99',
            'rkw_projects' => '8.7.0-8.7.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];