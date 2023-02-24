<?php

namespace RKW\RkwRelated\Controller;

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
use RKW\RkwRelated\Domain\Repository\SysCategoryRepository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class CategoryController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_Related
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CategoryController extends AbstractController
{

    /**
     * @var \RKW\RkwRelated\Domain\Repository\SysCategoryRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ?SysCategoryRepository $sysCategoryRepository;


    /**
     * showSelected
     *
     * @return void
     */
    public function showSelectedAction(): void
    {
        $this->view->assign(
            'categoryList',
            $this->sysCategoryRepository->findByUidList(
                GeneralUtility::trimExplode(',', $this->settings['categorySelect']['categories'])
            )
        );
    }
}
