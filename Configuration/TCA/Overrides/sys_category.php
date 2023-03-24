<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        $tempCols = [

            'tx_rkwrelated_link' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:rkw_related/Resources/Private/Language/locallang_db.xlf:tx_rkwrelated_domain_model_syscategory.tx_rkwrelated_link',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'pages',
                    'maxitems' => 1,
                    'minitems' => 0,
                    'size' => 1,
                    'default' => 0,
                    'suggestOptions' => [
                        'default' => [
                            'additionalSearchFields' => 'nav_title, alias, url',
                            'addWhere' => 'AND pages.doktype = 1'
                        ]
                    ]
                ]
            ],

        ];

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $tempCols);

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'sys_category',
            '
            tx_rkwrelated_link,
            ',
            '',
            'after:parent');

    },
    'rkw_related'
);
