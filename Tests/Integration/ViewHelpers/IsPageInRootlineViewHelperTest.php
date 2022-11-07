<?php
namespace RKW\RkwRelated\Tests\Integration\ViewHelpers;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * IsPageInRootlineViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRelated
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class IsPageInRootlineViewHelperTest extends FunctionalTestCase
{

    const FIXTURES_PATH = __DIR__ . '/IsPageInRootlineViewHelperTest/Fixtures';


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
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    private $standAloneViewHelper;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;



    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(self::FIXTURES_PATH .'/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_authors/Configuration/TypoScript/setup.txt',
                'EXT:rkw_projects/Configuration/TypoScript/setup.txt',
                'EXT:rkw_related/Configuration/TypoScript/setup.txt',
                'EXT:rkw_related/Tests/Integration/ViewHelpers/IsPageInRootlineViewHelperTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURES_PATH . '/Frontend/Templates'
            ]
        );
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsFalseIfNotInRootline ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template on a page
         * Given a pageUid is given
         * Given the pageUid is not in the same rootline as the current page
         * When the ViewHelper is rendered
         * Then it returns false
         */
        $this->importDataSet(self::FIXTURES_PATH . '/Database/Check10.xml');

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('pid', 4);
        $GLOBALS['TSFE']->id = 3;

        $result = preg_replace(
            '/\s/',
            '',
            $this->standAloneViewHelper->render()
        );

        self::assertEquals($result, 'FALSE');
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReturnsTrueIfInRootline ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template on a page
         * Given a pageUid is given
         * Given the pageUid is in the same rootline as the current page
         * When the ViewHelper is rendered
         * Then it returns true
         */
        $this->importDataSet(self::FIXTURES_PATH . '/Database/Check10.xml');

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('pid', 4);
        $GLOBALS['TSFE']->id = 5;

        $result = preg_replace(
            '/\s/',
            '',
            $this->standAloneViewHelper->render()
        );

        self::assertEquals($result, 'TRUE');
    }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
