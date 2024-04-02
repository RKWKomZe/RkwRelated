<?php
namespace RKW\RkwRelated\UserFunctions;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class FlexForm
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRelated
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FlexForm
{

    const TT_CONTENT_TABLE = 'tt_content';

    /**
     * tableName
     *
     * @var string
     */
    protected string $tableName = 'tx_rkwprojects_domain_model_projects';


    /**
     * configurationManager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?ConfigurationManagerInterface $configurationManager = null;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    public function injectConfigurationManagerInterface(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }


    /**
     * Returns true, if TSCONFIG entry exists
     *
     * @return bool
     */
    public function categoryTreeVisibility(): bool
    {
        // @toDo: Possible improvement idea: Instead of a static path we could read the given flexform property path and check for it

        $pageTsConfig = GeneralUtility::removeDotsFromTS(BackendUtility::getPagesTSconfig($this->getCurrentPageIdentifier()));
        // check either if path exists AND if there is a value!
        if (
            isset($pageTsConfig['TCEFORM']['tt_content']['pi_flexform']['rkwrelated_morecontent']['filteroptions']['settings.categoriesList']['config']['treeConfig']['rootUid'])
            && $pageTsConfig['TCEFORM']['tt_content']['pi_flexform']['rkwrelated_morecontent']['filteroptions']['settings.categoriesList']['config']['treeConfig']['rootUid']
        ) {
            return true;
        }

        return false;
    }


    /**
     * Returns items from a given database table (set specific PID via TsConfig, see following line)
     *
     * Example PID entry TsConfig: "TCEFORM.tx_rkwprojects_domain_model_projects.PAGE_TSCONFIG_IDLIST = 123456"
     *
     * Override table name:     <tableName>tx_myext_domain_model_example</tableName>
     * Set ordering:            <orderBy>name</orderBy>
     *
     * FlexForm example:
     *
     * <itemsProcFunc>RKW\RkwRelated\UserFunctions\FlexForm->filterRecordsByTsConfigPid</itemsProcFunc>
     * <parameters>
     *      <tableName>tx_rkwprojects_domain_model_projects</tableName>
     *      <orderBy>name</orderBy>
     * </parameters>
     *
     * @param array $flexForm
     * @return array
     */
    public function filterRecordsByTsConfigPid(array $flexForm): array
    {
        if (isset($flexForm['config']['parameters']['tableName'])){
            // override table name if set
            $this->tableName = $flexForm['config']['parameters']['tableName'];
        }

        $pageTsConfig = GeneralUtility::removeDotsFromTS(BackendUtility::getPagesTSconfig($this->getCurrentPageIdentifier()));
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->tableName);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->select('uid', 'name');
        $queryBuilder->from($this->tableName);

        $queryBuilder->where($queryBuilder->expr()->eq(
                'sys_language_uid',
                $queryBuilder->createNamedParameter(
                    $this->getContentLanguageId(),
                    \PDO::PARAM_INT
                )
            )
        );

        // THE MAGIC: START
        // IMPORTANT IF! ONLY USE SPECIFIC PID IF SET! OTHERWISE, USE WHOLE TABLE AS FALLBACK!
        if (!empty($pageTsConfig['TCEFORM'][$this->tableName]['PAGE_TSCONFIG_IDLIST'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $pageTsConfig['TCEFORM'][$this->tableName]['PAGE_TSCONFIG_IDLIST'],
                        \PDO::PARAM_INT
                    )
                )
            );
        }
        // THE MAGIC: END

        if (isset($flexForm['config']['parameters']['orderBy'])) {
            $queryBuilder->orderBy($flexForm['config']['parameters']['orderBy']);
        }

        $statement = $queryBuilder->execute();
        while ($entity = $statement->fetch()) {
            // Add results to item list
            $flexForm['items'][] = [$entity['name'], $entity['uid']];
        }

        return $flexForm;
    }


    /**
     * @return int
     */
    protected function getCurrentPageIdentifier(): int
    {
        $contentIdentifier = $this->getCurrentContentIdentifier();
        if ($contentIdentifier > 0) {
            return $this->getPageIdentifierFromContentIdentifier($contentIdentifier);
        }
        return 0;
    }


    /**
     * @return int
     */
    protected function getCurrentContentIdentifier(): int
    {
        $edit = GeneralUtility::_GP('edit');
        if (!empty($edit[self::TT_CONTENT_TABLE])) {
            return (int)key($edit[self::TT_CONTENT_TABLE]);
        }
        return 0;
    }


    /**
     * @param int $contentIdentifier
     * @return int
     */
    protected function getPageIdentifierFromContentIdentifier(int $contentIdentifier): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TT_CONTENT_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        return (int)$queryBuilder
            ->select('pid')
            ->from(self::TT_CONTENT_TABLE)
            ->where('uid=' . (int)$contentIdentifier)
            ->execute()
            ->fetchColumn();
    }


    /**
     * @return int
     */
    protected function getContentLanguageId(): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TT_CONTENT_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        return (int)$queryBuilder
            ->select('sys_language_uid')
            ->from(self::TT_CONTENT_TABLE)
            ->where('uid=' . (int)$this->getCurrentContentIdentifier())
            ->execute()
            ->fetchColumn();
    }

}
