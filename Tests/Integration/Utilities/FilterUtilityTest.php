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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FilterUtilityTest extends FunctionalTestCase
{

    const IMPORT_PATH = __DIR__ . '/FilterUtilityTest/Fixtures/Database';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_authors',
        'typo3conf/ext/rkw_projects',
        'typo3conf/ext/rkw_related'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \RKW\RkwRelated\Utilities\FilterUtility
     */
    protected $subject;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;



    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {

        parent::setUp();

        $this->importDataSet(self::IMPORT_PATH .'/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_authors/Configuration/TypoScript/setup.txt',
                'EXT:rkw_projects/Configuration/TypoScript/setup.txt',
                'EXT:rkw_related/Configuration/TypoScript/setup.txt',
                'EXT:rkw_related/Tests/Integration/FilterUtilityTest/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(FilterUtility::class);

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
        $result = $this->subject->getExcludePidList([]);

        static::assertCount(1, $result);
        static::assertEquals(15, $result[0]);
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

        $result = $this->subject->getExcludePidList($settings);

        static::assertCount(2, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(16, $result[1]);

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

        $result = $this->subject->getExcludePidList($settings);

        static::assertCount(5, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(16, $result[1]);
        static::assertEquals(17, $result[2]);
        static::assertEquals(18, $result[3]);
        static::assertEquals(19, $result[4]);

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
        $result = $this->subject->getIncludePidList([]);
        static::assertCount(2, $result);
        static::assertEquals(1, $result[0]);
        static::assertEquals(2, $result[1]);

    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check20.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(5, $result);
        static::assertEquals(161, $result[0]);
        static::assertEquals(1611, $result[1]);
        static::assertEquals(1612, $result[2]);
        static::assertEquals(162, $result[3]);
        static::assertEquals(1621, $result[4]);
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check10.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(4, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(151, $result[1]);
        static::assertEquals(152, $result[2]);
        static::assertEquals(1521, $result[3]);
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

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(2, $result);
        static::assertEquals(1, $result[0]);
        static::assertEquals(2, $result[1]);

    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check30.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(4, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(151, $result[1]);
        static::assertEquals(152, $result[2]);
        static::assertEquals(1521, $result[3]);
    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check30.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(5, $result);
        static::assertEquals(161, $result[0]);
        static::assertEquals(1611, $result[1]);
        static::assertEquals(1612, $result[2]);
        static::assertEquals(162, $result[3]);
        static::assertEquals(1621, $result[4]);
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check40.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(2, $result);
        static::assertEquals(17, $result[0]);
        static::assertEquals(18, $result[1]);
    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check40.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(6, $result);
        static::assertEquals(17, $result[0]);
        static::assertEquals(171, $result[1]);
        static::assertEquals(18, $result[2]);
        static::assertEquals(181, $result[3]);
        static::assertEquals(1811, $result[4]);
        static::assertEquals(1812, $result[5]);
    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
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

        $this->importDataSet(self::IMPORT_PATH .'/Check50.xml');

        $result = $this->subject->getIncludePidList($settings);
        static::assertCount(2, $result);
        static::assertEquals(17, $result[0]);
        static::assertEquals(18, $result[1]);
    }

    //=============================================

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getCombinedFilterByNameReturnsFilterBySettings()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured
         * When getCombinedFilterByName is called
         * Then the configured filter is returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings);
        static::assertCount(3, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(16, $result[1]);
        static::assertEquals(18, $result[2]);

    }

    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsSanitizedFilterBySettings()
    {

        /**
         * Scenario:
         *
         * Given a filter is configured
         * Given that filter contains invalid characters
         * When getCombinedFilterByName is called
         * Then the sanitized configured filter is returned
         */
        $settings = [
            'documentTypeList' => '>!,15,16-);,18',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings);
        static::assertCount(3, $result);
        static::assertEquals(15, $result[0]);
        static::assertEquals(16, $result[1]);
        static::assertEquals(18, $result[2]);

    }

    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given an external filter is given
         * When getCombinedFilterByName is called
         * Then the external filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '25,26,28',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings, $externalFilter);
        static::assertCount(3, $result);
        static::assertEquals(25, $result[0]);
        static::assertEquals(26, $result[1]);
        static::assertEquals(28, $result[2]);

    }

    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsSanitizedExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given an external filter is given
         * Given that filter contains invalid characters
         * When getCombinedFilterByName is called
         * Then the sanitized external filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '&(&;-25,<<>&%26,28',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings, $externalFilter);
        static::assertCount(3, $result);
        static::assertEquals(25, $result[0]);
        static::assertEquals(26, $result[1]);
        static::assertEquals(28, $result[2]);

    }

    /**
     * @test
     */
    public function getCombinedFilterByNameIgnoresEmptyExternalFilter()
    {

        /**
         * Scenario:
         *
         * Given an external filter is given
         * Given that filter contains a zero
         * When getCombinedFilterByName is called
         * Then no filter is returned
         */
        $settings = [];
        $externalFilter = [
            'documentType' => '0',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings, $externalFilter);
        static::assertCount(0, $result);

    }

    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilterOverSettingsFilter()
    {

        /**
         * Scenario:
         *
         * Given an external filter is given
         * Given a filter is configured
         * When getCombinedFilterByName is called
         * Then the external filter is returned
         */
        $settings = [
            'documentTypeList' => '15,16,18',

        ];
        $externalFilter = [
            'documentType' => '25,26,28',
        ];

        $result = $this->subject->getCombinedFilterByName('documentType', $settings, $externalFilter);
        static::assertCount(3, $result);
        static::assertEquals(25, $result[0]);
        static::assertEquals(26, $result[1]);
        static::assertEquals(28, $result[2]);
    }

    /**
     * @test
     */
    public function getCombinedFilterByNameReturnsExternalFilterOverSettingsFilterIfExternalZeroValue()
    {

        /**
         * Scenario:
         *
         * Given an external filter is given
         * Given a filter is configured
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

        $result = $this->subject->getCombinedFilterByName('documentType', $settings, $externalFilter);
        static::assertCount(0, $result);

    }



    //=============================================

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getProjectRecursiveReturnsProjectOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has a project set
         * When getProjectRecursive is called
         * Then this project is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check60.xml');
        $GLOBALS['TSFE']->id = 300;

        /** @var \RKW\RkwProjects\Domain\Model\Projects $result */
        $result = $this->subject->getProjectRecursive();
        static::assertInstanceOf('\RKW\RkwProjects\Domain\Model\Projects', $result);
        static::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getProjectRecursiveReturnsProjectOfPageInRootline()
    {

        /**
         * Scenario:
         *
         * Given the current page has no project set
         * Given the parent of the parent of the current page has a project set
         * When getProjectRecursive is called
         * Then this project is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check70.xml');
        $GLOBALS['TSFE']->id = 3000;

        /** @var \RKW\RkwProjects\Domain\Model\Projects $result */
        $result = $this->subject->getProjectRecursive();
        static::assertInstanceOf('\RKW\RkwProjects\Domain\Model\Projects', $result);
        static::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getProjectRecursiveReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given the current page has no project set
         * Given no other page in the rootline has a project set
         * When getProjectRecursive is called
         * Then null is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check80.xml');
        $GLOBALS['TSFE']->id = 3000;

        $result = $this->subject->getProjectRecursive();
        static::assertNull($result);
    }


    //=============================================

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getSysCategoriesReturnsCategoriesOfProject()
    {

        /**
         * Scenario:
         *
         * Given the current page has a project set
         * Given that project has two categories set
         * When getSysCategories is called
         * Then the two categories of the project are returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check90.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject->getSysCategories();
        static::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        static::assertCount(2, $result);

        $result = $result->toArray();
        static::assertEquals(1, $result[0]->getUid());
        static::assertEquals(2, $result[1]->getUid());

    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getSysCategoriesReturnsCategoriesOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has two categories set
         * When getSysCategories is called
         * Then the two categories of the page are returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check100.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject->getSysCategories();
        static::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        static::assertCount(2, $result);

        $result = $result->toArray();
        static::assertEquals(1, $result[0]->getUid());
        static::assertEquals(2, $result[1]->getUid());

    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getSysCategoriesReturnsCategoriesOfPageAsFallback()
    {

        /**
         * Scenario:
         *
         * Given the current page as a project set
         * Given that project has no categories set
         * Given the current page has two categories set
         * When getSysCategories is called
         * Then the two categories of the page are returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check110.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject->getSysCategories();
        static::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        static::assertCount(2, $result);

        $result = $result->toArray();
        static::assertEquals(1, $result[0]->getUid());
        static::assertEquals(2, $result[1]->getUid());

    }

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getSysCategoriesReturnsCategoriesOfProjectOverPage()
    {

        /**
         * Scenario:
         *
         * Given the current page as a project set
         * Given that project has two categories set
         * Given the current page has two categories set
         * When getSysCategories is called
         * Then the two categories of the project are returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check120.xml');
        $GLOBALS['TSFE']->id = 400;

        /** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $result */
        $result = $this->subject->getSysCategories();
        static::assertInstanceOf('\\TYPO3\CMS\Extbase\Persistence\ObjectStorage', $result);
        static::assertCount(2, $result);

        $result = $result->toArray();
        static::assertEquals(3, $result[0]->getUid());
        static::assertEquals(4, $result[1]->getUid());

    }

    //=============================================

    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getDepartmentRecursiveReturnsDepartmentOfPage()
    {

        /**
         * Scenario:
         *
         * Given the current page has a department set
         * When getDepartmentRecursive is called
         * Then this department is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check130.xml');
        $GLOBALS['TSFE']->id = 300;

        /** @var \RKW\RkwBasics\Domain\Model\Department $result */
        $result = $this->subject->getDepartmentRecursive();
        static::assertInstanceOf('\RKW\RkwBasics\Domain\Model\Department', $result);
        static::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getDepartmentRecursiveReturnsDepartmentOfPageInRootline()
    {

        /**
         * Scenario:
         *
         * Given the current page has no department set
         * Given the parent of the parent of the current page has a department set
         * When getDepartmentRecursive is called
         * Then this department is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check140.xml');
        $GLOBALS['TSFE']->id = 3000;

        /** @var \RKW\RkwBasics\Domain\Model\Department $result */
        $result = $this->subject->getDepartmentRecursive();
        static::assertInstanceOf('\RKW\RkwBasics\Domain\Model\Department', $result);
        static::assertEquals(1, $result->getUid());
    }


    /**
     * @test
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    public function getDepartmentRecursiveReturnsNull()
    {

        /**
         * Scenario:
         *
         * Given the current page has no department set
         * Given no other page in the rootline has a department set
         * When getDepartmentRecursive is called
         * Then null is returned
         */
        $this->importDataSet(self::IMPORT_PATH .'/Check150.xml');
        $GLOBALS['TSFE']->id = 3000;

        $result = $this->subject->getDepartmentRecursive();
        static::assertNull($result);
    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}