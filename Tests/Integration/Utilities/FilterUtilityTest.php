<?php
namespace RKW\RkwRelated\Tests\Integration\Utilities;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRelated\Utilities\FilterUtility;
use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * FilterUtilityTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRelated
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FilterUtilityTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FilterUtilityTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_projects',
        'typo3conf/ext/rkw_related'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [
        'seo'
    ];


    /**
     * @var \RKW\RkwRelated\Utilities\FilterUtility|null
     */
    protected ?FilterUtility $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    protected ?ObjectManager $objectManager = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH. '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_authors/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_projects/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_related/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ],
            ['example.com' => self::FIXTURE_PATH .  '/Frontend/Configuration/config.yaml']

        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(FilterUtility::class);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

    }

    //=============================================

    /**
     * @test
     */
    public function getExcludePidListReturnsFrontendPid()
    {

        /**
         * Scenario:
         *
         * Given a FE-pid is active
         * When getExcludePidList is called
         * Then this FE-pid is returned
         */

        $GLOBALS['TSFE']->id = 15;
        $result = $this->subject::getExcludePidList([]);

        self::assertCount(1, $result);
        self::assertEquals(15, $result[0]);
    }


    /**
     * @test
     */
    public function getExcludePidListIncludesStartingPid()
    {

        /**
         * Scenario:
         *
         * Given a FE-pid is active
         * Given another pid is defined as starting pid
         * When getExcludePidList is called
         * Then both pids are returned
         */
        $GLOBALS['TSFE']->id = 15;
        $settings = [
            'startingPid' => 16
        ];

        $result = $this->subject::getExcludePidList($settings);

        self::assertCount(2, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(16, $result[1]);

    }


    /**
     * @test
     */
    public function getExcludePidListIncludesExcludePids()
    {

        /**
         * Scenario:
         *
         * Given a FE-pid is active
         * Given a list of exclude pids is configured
         * When getExcludePidList is called
         * Then all pids are returned
         */
        $GLOBALS['TSFE']->id = 15;
        $settings = [
            'startingPid' => 16,
            'excludePidList' => '17, 18 ,19 '
        ];

        $result = $this->subject::getExcludePidList($settings);

        self::assertCount(5, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(16, $result[1]);
        self::assertEquals(17, $result[2]);
        self::assertEquals(18, $result[3]);
        self::assertEquals(19, $result[4]);

    }

    //=============================================

    /**
     * @test
     */
    public function getIncludePidListUsesDefaultRootLine()
    {

        /**
         * Scenario:
         *
         * Given nothing is configured
         * When getIncludePidList is called
         * Then the default rootline is returned
         */
        $result = $this->subject::getIncludePidList([]);
        self::assertCount(2, $result);
        self::assertEquals(1, $result[0]);
        self::assertEquals(2, $result[1]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesStartingPidList()
    {

        /**
         * Scenario:
         *
         * Given a startingPidList is configured
         * When getIncludePidList is called
         * Then the rootline of the startingPidList is returned
         */
        $settings = [
            'startingPidList' => '161,162'
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check20.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(5, $result);
        self::assertEquals(161, $result[0]);
        self::assertEquals(1611, $result[1]);
        self::assertEquals(1612, $result[2]);
        self::assertEquals(162, $result[3]);
        self::assertEquals(1621, $result[4]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesExistingStartingPid()
    {

        /**
         * Scenario:
         *
         * Given a startingPid is configured
         * Given this pid does exist
         * When getIncludePidList is called
         * Then the rootline of the startingPid is returned
         */
        $settings = [
            'startingPid' => 15
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check10.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(4, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(151, $result[1]);
        self::assertEquals(152, $result[2]);
        self::assertEquals(1521, $result[3]);
    }


    /**
     * @test
     */
    public function getIncludePidListIgnoresNonExistentStartingPid()
    {

        /**
         * Scenario:
         *
         * Given a startingPid is configured
         * Given this pid does not exist
         * When getIncludePidList is called
         * Then the basic rootline is returned
         */
        $settings = [
            'startingPid' => 15
        ];

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(2, $result);
        self::assertEquals(1, $result[0]);
        self::assertEquals(2, $result[1]);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesExistingStartingPidOverStartingPidList()
    {

        /**
         * Scenario:
         *
         * Given a startingPid is configured
         * Given this pid does exist
         * Given a startingPidList is configured
         * When getIncludePidList is called
         * Then the rootline of the startingPid is returned
         */
        $settings = [
            'startingPid' => 15,
            'startingPidList' => '161,162'

        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check30.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(4, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(151, $result[1]);
        self::assertEquals(152, $result[2]);
        self::assertEquals(1521, $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesStartingPidListOverNonExistingStartingPidGiven()
    {

        /**
         * Scenario:
         *
         * Given a startingPid is configured
         * Given this pid does not exist
         * Given a startingPidList is configured
         * When getIncludePidList is called
         * Then the rootline of the startingPidList is returned
         */
        $settings = [
            'startingPid' => 99,
            'startingPidList' => '161,162'
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check30.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(5, $result);
        self::assertEquals(161, $result[0]);
        self::assertEquals(1611, $result[1]);
        self::assertEquals(1612, $result[2]);
        self::assertEquals(162, $result[3]);
        self::assertEquals(1621, $result[4]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesPidList()
    {

        /**
         * Scenario:
         *
         * Given a pidList is configured
         * When getIncludePidList is called
         * Then the pidList is returned
         */
        $settings = [
            'pidList' => '17,18'
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check40.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(2, $result);
        self::assertEquals(17, $result[0]);
        self::assertEquals(18, $result[1]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesPidListRecursive()
    {

        /**
         * Scenario:
         *
         * Given a pidList is configured
         * Given pidListRecursive is true
         * When getIncludePidList is called
         * Then the rootline of the pidList is returned
         */
        $settings = [
            'pidList' => '17,18',
            'pidListRecursive' => true
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check40.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(6, $result);
        self::assertEquals(17, $result[0]);
        self::assertEquals(171, $result[1]);
        self::assertEquals(18, $result[2]);
        self::assertEquals(181, $result[3]);
        self::assertEquals(1811, $result[4]);
        self::assertEquals(1812, $result[5]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIncludePidListUsesPidListOverEverything()
    {

        /**
         * Scenario:
         *
         * Given a startingPid is configured
         * Given this pid does exist
         * Given a startingPidList is configured
         * Given a pidList is configured
         * When getIncludePidList is called
         * Then the pidList is returned
         */
        $settings = [
            'startingPid' => 15,
            'startingPidList' => '161,162',
            'pidList' => '17,18',
        ];

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check50.xml');

        $result = $this->subject::getIncludePidList($settings);
        self::assertCount(2, $result);
        self::assertEquals(17, $result[0]);
        self::assertEquals(18, $result[1]);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getCombinedFilterByNameReturnsFilterBySettings()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given this filter configured via settings is not a pagePropertyFilter
         * When getCombinedFilterByName is called
         * Then the configured filter is returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings);
        self::assertCount(3, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(16, $result[1]);
        self::assertEquals(18, $result[2]);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameChecksForValidNames()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given this filter configured via settings is not a pagePropertyFilter
         * When getCombinedFilterByName is called with invalid filter-name
         * Then no filter returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',
        ];

        $result = $this->subject::getCombinedFilterByName('hurts', $settings);
        self::assertCount(0, $result);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsSanitizedFilterBySettings()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given this filter configured via settings is not a pagePropertyFilter
         * Given that filter contains invalid characters
         * When getCombinedFilterByName is called
         * Then the sanitized configured filter is returned
         */
        $settings = [
            'documentTypeList' => '>!,15,16-);,18',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings);
        self::assertCount(3, $result);
        self::assertEquals(15, $result[0]);
        self::assertEquals(16, $result[1]);
        self::assertEquals(18, $result[2]);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given no settings are configured
         * Given an external filter is set
         * When getCombinedFilterByName is called
         * Then the external filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '25,26,28',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(3, $result);
        self::assertEquals(25, $result[0]);
        self::assertEquals(26, $result[1]);
        self::assertEquals(28, $result[2]);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilterWhenAsArray()
    {

        /**
         * Scenario:
         *
         * Given no settings are configured
         * Given an external filter is given
         * Given the external filter is given as array
         * When getCombinedFilterByName is called
         * Then the external filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => [
                25,
                26,
                28
            ]
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(3, $result);
        self::assertEquals(25, $result[0]);
        self::assertEquals(26, $result[1]);
        self::assertEquals(28, $result[2]);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsSanitizedExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given no settings are configured
         * Given an external filter is given
         * Given that filter contains invalid characters
         * When getCombinedFilterByName is called
         * Then the sanitized external filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '&(&;-25,<<>&%26,28',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(3, $result);
        self::assertEquals(25, $result[0]);
        self::assertEquals(26, $result[1]);
        self::assertEquals(28, $result[2]);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameIgnoresEmptyExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given no settings are configured
         * Given an external filter is given
         * Given that filter contains a zero
         * When getCombinedFilterByName is called
         * Then no filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '0',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(0, $result);

    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilterOverSettingsFilter()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given this filter configured via settings is not a pagePropertyFilter
         * Given an external filter for the same filter-type is given
         * When getCombinedFilterByName is called
         * Then the external filter is returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',

        ];
        $externalFilter = [
            'documentType' => '25,26,28',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(3, $result);
        self::assertEquals(25, $result[0]);
        self::assertEquals(26, $result[1]);
        self::assertEquals(28, $result[2]);
    }


    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilterOverSettingsFilterIfExternalZeroValue()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given this filter configured via settings is not a pagePropertyFilter
         * Given an external filter for the same filter-type is given
         * Given the external filter has the value of zero
         * When getCombinedFilterByName is called
         * Then the configured filter is returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',
        ];
        $externalFilter = [
            'documentType' => '0',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(3, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getCombinedFilterByNameReturnsAlwaysPagePropertyFilterWhenSet()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given a pagePropertyFilter for the same filter-type is also set via settings
         * Given an external filter for the same filter-type is given
         * When getCombinedFilterByName is called
         * Then the pagePropertyFilter is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check180.xml');
        $GLOBALS['TSFE']->id = 180;

        $settings = [
            'documentTypeList' => '15,16,18',
            'pagePropertyFilter' => 'documentType,department'

        ];
        $externalFilter = [
            'documentType' => '25,26,28',
        ];

        $result = $this->subject::getCombinedFilterByName('documentType', $settings, $externalFilter);
        self::assertCount(1, $result);
        self::assertEquals(2, $result[0]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getCombinedFilterBySpecificDepartmentReturnsThatDepartment()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given a pagePropertyFilter for the same filter-type is also set via settings
         * Given an external filter for the same filter-type is given
         * When getCombinedFilterByName is called
         * Then the pagePropertyFilter is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check190.xml');
        $GLOBALS['TSFE']->id = 190;

        $settings = [
            'departmentList' => '1, 2',
            'pagePropertyFilter' => 'department'
        ];
        $externalFilter = [
            'department' => '1',
        ];

        $result = $this->subject::getCombinedFilterByName('department', $settings, $externalFilter);
        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getCombinedFilterForDepartmentByAllDepartmentsReturnsNoDepartmentRecords()
    {

        /**
         * SPECIAL CASE! IF NO DEPARTMENT IS SELECTED, RETURN 0! AND NOT THE DEFAULT "departmentList" !
         * (The value "0" will ensure, that the repository is searching in all department-records)
         *
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given a pagePropertyFilter for the same filter-type is also set via settings
         * Given an external filter for the same filter-type is given
         * When getCombinedFilterByName is called
         * Then NOTHING is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check190.xml');
        $GLOBALS['TSFE']->id = 190;

        $settings = [
            'departmentList' => '1, 2, 4',
            'pagePropertyFilter' => 'department'
        ];
        $externalFilter = [
            'department' => '0',
        ];

        $result = $this->subject::getCombinedFilterForDepartment($settings, $externalFilter);
        self::assertCount(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getCombinedFilterForDepartmentByNoExternalFilterReturnsDefaultDepartmentRecords()
    {

        /**
         * SPECIAL CASE! IF NO DEPARTMENT IS SELECTED, RETURN 0! AND NOT THE DEFAULT "departmentList" !
         * (The value "0" will ensure, that the repository is searching in all department-records)
         *
         * Scenario:
         *
         * Given a filter is configured via settings
         * Given a pagePropertyFilter for the same filter-type is also set via settings
         * Given an external filter for the same filter-type is given
         * When getCombinedFilterByName is called
         * Then NOTHING is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check190.xml');
        $GLOBALS['TSFE']->id = 190;

        $settings = [
            'departmentList' => '1, 2, 4',
            'pagePropertyFilter' => 'department'
        ];
        $externalFilter = [
            'department' => null,
        ];

        $result = $this->subject::getCombinedFilterForDepartment($settings, $externalFilter);
        self::assertCount(3, $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPagePropertyFiltersReturnsFilters ()
    {

        /**
         * Scenario:
         *
         * Given a pagePropertyFilter is configured
         * Give the pagePropertyFilter is set with two valid values
         * Given both configured values are set as properties in the page properties
         * When getPagePropertyFilters is called
         * Then both filters are returned with their object uids
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check160.xml');
        $GLOBALS['TSFE']->id = 160;

        $settings = [
            'pagePropertyFilter' => 'department,documentType',
        ];

        $result = $this->subject::getPagePropertyFilters($settings);
        self::assertCount(2, $result);
        self::assertEquals(1, $result['department']);
        self::assertEquals(2, $result['documentType']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPagePropertyFiltersReturnsEmptyIfNotSet ()
    {

        /**
         * Scenario:
         *
         * Given no pagePropertyFilter is configured
         * Given two valid values are set as properties in the page properties
         * When getPagePropertyFilters is called
         * Then no filter is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check160.xml');
        $GLOBALS['TSFE']->id = 160;

        $settings = [];

        $result = $this->subject::getPagePropertyFilters($settings);
        self::assertCount(0, $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPagePropertyFiltersReturnsIgnoresInvalid ()
    {

        /**
         * Scenario:
         *
         * Given a pagePropertyFilter is configured
         * Given the pagePropertyFilter is set with two values
         * Given one of the values in invalid
         * Given two valid values are set as properties in the page properties
         * When getPagePropertyFilters is called
         * Then only the valid filter is returned with the object uid
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check160.xml');
        $GLOBALS['TSFE']->id = 160;

        $settings = [
            'pagePropertyFilter' => 'departmenten,documentType',
        ];

        $result = $this->subject::getPagePropertyFilters($settings);
        self::assertCount(1, $result);
        self::assertEquals(2, $result['documentType']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPagePropertyFiltersReturnsIgnoresNotDefined ()
    {

        /**
         * Scenario:
         *
         * Given a pagePropertyFilter is configured
         * Give the pagePropertyFilter is set with two valid values
         * Given only one configured value is defined as property in the page properties
         * When getPagePropertyFilters is called
         * Then only the filter defined in page properties is returned with the object uid
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check170.xml');
        $GLOBALS['TSFE']->id = 170;

        $settings = [
            'pagePropertyFilter' => 'department,documentType',
        ];

        $result = $this->subject::getPagePropertyFilters($settings);
        self::assertCount(1, $result);
        self::assertEquals(2, $result['documentType']);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPageProjectRecursiveReturnsProjectOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has a project set
         * When getPageProjectRecursive is called
         * Then this project is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check60.xml');
        $GLOBALS['TSFE']->id = 300;

        /** @var \RKW\RkwProjects\Domain\Model\Projects $result */
        $result = $this->subject::getPageProjectRecursive();
        self::assertInstanceOf('\RKW\RkwProjects\Domain\Model\Projects', $result);
        self::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageProjectRecursiveReturnsProjectOfPageInRootline()
    {

        /**
         * Scenario:
         *
         * Given the current page has no project set
         * Given the parent of the parent of the current page has a project set
         * When getPageProjectRecursive is called
         * Then this project is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check70.xml');
        $GLOBALS['TSFE']->id = 3000;

        /** @var \RKW\RkwProjects\Domain\Model\Projects $result */
        $result = $this->subject::getPageProjectRecursive();
        self::assertInstanceOf('\RKW\RkwProjects\Domain\Model\Projects', $result);
        self::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageProjectRecursiveReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given the current page has no project set
         * Given no other page in the rootline has a project set
         * When getPageProjectRecursive is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check80.xml');
        $GLOBALS['TSFE']->id = 3000;

        $result = $this->subject::getPageProjectRecursive();
        self::assertNull($result);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPageSysCategoriesReturnsCategoriesOfProject()
    {

        /**
         * Scenario:
         *
         * Given the current page has a project set
         * Given that project has two categories set
         * When getPageSysCategories is called
         * Then the two categories of the project are returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check90.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject::getPageSysCategories();
        self::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        self::assertCount(2, $result);

        $result = $result->toArray();
        self::assertEquals(1, $result[0]->getUid());
        self::assertEquals(2, $result[1]->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageSysCategoriesReturnsCategoriesOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has two categories set
         * When getPageSysCategories is called
         * Then the two categories of the page are returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check100.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject::getPageSysCategories();
        self::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        self::assertCount(2, $result);

        $result = $result->toArray();
        self::assertEquals(1, $result[0]->getUid());
        self::assertEquals(2, $result[1]->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageSysCategoriesReturnsCategoriesOfPageAsFallback()
    {

        /**
         * Scenario:
         *
         * Given the current page as a project set
         * Given that project has no categories set
         * Given the current page has two categories set
         * When getPageSysCategories is called
         * Then the two categories of the page are returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check110.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject::getPageSysCategories();
        self::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        self::assertCount(2, $result);

        $result = $result->toArray();
        self::assertEquals(1, $result[0]->getUid());
        self::assertEquals(2, $result[1]->getUid());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageSysCategoriesReturnsCategoriesOfProjectOverPage()
    {

        /**
         * Scenario:
         *
         * Given the current page as a project set
         * Given that project has two categories set
         * Given the current page has two categories set
         * When getPageSysCategories is called
         * Then the two categories of the project are returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check120.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject::getPageSysCategories();
        self::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        self::assertCount(2, $result);

        $result = $result->toArray();
        self::assertEquals(3, $result[0]->getUid());
        self::assertEquals(4, $result[1]->getUid());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPageDepartmentRecursiveReturnsDepartmentOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has a department set
         * When getPageDepartmentRecursive is called
         * Then this department is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check130.xml');
        $GLOBALS['TSFE']->id = 300;

        /** @var \RKW\RkwBasics\Domain\Model\Department $result */
        $result = $this->subject::getPageDepartmentRecursive();
        self::assertInstanceOf('\RKW\RkwBasics\Domain\Model\Department', $result);
        self::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageDepartmentRecursiveReturnsDepartmentOfPageInRootline()
    {

        /**
         * Scenario:
         *
         * Given the current page has no department set
         * Given the parent of the parent of the current page has a department set
         * When getPageDepartmentRecursive is called
         * Then this department is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check140.xml');
        $GLOBALS['TSFE']->id = 3000;

        /** @var \RKW\RkwBasics\Domain\Model\Department $result */
        $result = $this->subject::getPageDepartmentRecursive();
        self::assertInstanceOf('\RKW\RkwBasics\Domain\Model\Department', $result);
        self::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPageDepartmentRecursiveReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given the current page has no department set
         * Given no other page in the rootline has a department set
         * When getPageDepartmentRecursive is called
         * Then null is returned
         */
        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check150.xml');
        $GLOBALS['TSFE']->id = 3000;

        $result = $this->subject::getPageDepartmentRecursive();
        self::assertNull($result);
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        FrontendSimulatorUtility::resetFrontendEnvironment();
    }








}
