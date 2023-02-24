<?php

namespace RKW\RkwRelated\Domain\Repository;

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

use SJBR\StaticInfoTables\Domain\Model\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * SysCategoryRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SysCategoryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setRespectSysLanguage(false);
    }


    /**
     * Return not finished events
     * For the FIRST result page (just with simple limit)
     *
     * @param array $categoryList
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByUidList(array $categoryList): QueryResultInterface
    {
        $query = $this->createQuery();

        $constraints[] = $query->logicalNot($query->equals('title', ''));
        $constraints[] = $query->in('uid', $categoryList);

        return $query->matching(
            $query->logicalAnd(array_filter($constraints))
        )
            ->setOrderings(
                array(
                    'title' => QueryInterface::ORDER_ASCENDING,
                )
            )
            ->execute();
    }


    /**
     * get category list recursive by set parent(s)
     *
     * @param array $parentCategoryList One or more parent categories as objects or ID's
     * @param array $categoryList Is build by recursive handling by the function itself
     * @return array
     */
    public function findByParentRecursive(array $parentCategoryList, array &$categoryList = []): array
    {
        foreach ($parentCategoryList as $parentCategory) {
            $categoryId = $parentCategory instanceof AbstractEntity ? $parentCategory->getUid() : $parentCategory;
            $childrenCategoryList = $this->findByParent($categoryId)->toArray();

            if ($childrenCategoryList) {
                $this->findByParentRecursive($childrenCategoryList, $categoryList);
            }

            foreach ($childrenCategoryList as $childCategory) {
                $categoryList[] = $childCategory;
            }
        }
        return $categoryList;
    }


}
