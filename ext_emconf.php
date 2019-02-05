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

$EM_CONF[$_EXTKEY] = array(
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
	'version' => '7.6.4',
	'constraints' => array(
		'depends' => array(
            'extbase' => '7.6.0-7.6.99',
            'fluid' => '7.6.0-7.6.99',
            'typo3' => '7.6.0-7.6.99',
            'rkw_basics' => '7.6.10-8.7.99',
            'rkw_projects' => '7.6.10-8.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);