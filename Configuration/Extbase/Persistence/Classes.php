<?php
declare(strict_types = 1);

return [
    \RKW\RkwRelated\Domain\Model\Pages::class => [
        'tableName' => 'pages',
        'properties' => [
            'uid' => [
                'fieldName' => 'uid'
            ],
            'pid' => [
                'fieldName' => 'pid'
            ],
            'sysLanguageUid' => [
                'fieldName' => 'sys_language_uid'
            ],
            'tstamp' => [
                'fieldName' => 'tstamp'
            ],
            'lastUpdated' => [
                'fieldName' => 'lastUpdated'
            ],
        ],
    ],
    \RKW\RkwRelated\Domain\Model\TtContent::class => [
        'tableName' => 'tt_content',
    ],
    \RKW\RkwRelated\Domain\Model\SysCategory::class => [
        'tableName' => 'sys_category',
    ],
    \RKW\RkwRelated\Domain\Model\File::class => [
        'tableName' => 'sys_file',
    ],
    \RKW\RkwRelated\Domain\Model\FileReference ::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'file' => [
                'fieldName' => 'uid_local'
            ],
        ],
    ],
];
