<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
    {

        //=================================================================
        // Register Plugin
        //=================================================================
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRelated',
            'Similarcontent',
            'RKW Related: Das könnte Sie auch interessieren'
        );

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRelated',
            'Morecontent',
            'RKW Related: Mehr zum Thema'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRelated',
            'Morecontent2',
            'RKW Related: Mehr zum Thema II'
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'RKW.RkwRelated',
            'Selectedcategories',
            'RKW Related: Kategorien (Auswahl)'
        );



        //=================================================================
        // Add Flexform
        //=================================================================
        $extKey = 'rkw_related';
        $extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($extKey));

        // Check for rkw_projects and rkw_pdf2content
        if (
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')
            && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')
        ) {

            $pluginName = strtolower('Morecontent');
            $pluginSignature = $extensionName . '_' . $pluginName;
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                $pluginSignature,
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/MoreContentFullSpectrum.xml'
            );

            $pluginName = strtolower('Morecontent2');
            $pluginSignature = $extensionName . '_' . $pluginName;
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                $pluginSignature,
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/MoreContentFullSpectrum.xml'
            );

        } else {

            $pluginName = strtolower('Morecontent');
            $pluginSignature = $extensionName . '_' . $pluginName;
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                $pluginSignature,
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/MoreContent.xml'
            );

            $pluginName = strtolower('Morecontent2');
            $pluginSignature = $extensionName . '_' . $pluginName;
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
                $pluginSignature,
                'FILE:EXT:' . $extKey . '/Configuration/FlexForms/MoreContent.xml'
            );
        }

        $pluginName = strtolower('Similarcontent');
        $pluginSignature = $extensionName . '_' . $pluginName;
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            'FILE:EXT:' . $extKey . '/Configuration/FlexForms/SimilarContent.xml'
        );

        $pluginName = strtolower('Selectedcategories');
        $pluginSignature = $extensionName . '_' . $pluginName;
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            'FILE:EXT:' . $extKey . '/Configuration/FlexForms/SelectedCategories.xml'
        );

    },
    'rkw_related'
);
