<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Similarcontent',
	'RKW Related: Das könnte Sie auch interessieren'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Morecontent',
	'RKW Related: Mehr zum Thema'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Morecontent2',
	'RKW Related: Mehr zum Thema II'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'RKW.' . $_EXTKEY,
	'Morecontentpublication',
	'RKW Related: Mehr zum Thema - Publikationen'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'RKW Related');

//=================================================================
// Add Flexform
//=================================================================

$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));

// Proof rkw_projects and bm_pdf2content (rkw_projects depends on bm_pdf2content, so rkw_projects is sufficient)
if (
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')
	&& \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')
) {

	$pluginName = strtolower('Morecontent');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContentFullSpectrum.xml');

	$pluginName = strtolower('Morecontent2');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContentFullSpectrum.xml');

	$pluginName = strtolower('MorecontentPublication');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContentPublicationFullSpectrum.xml');

} else {

	$pluginName = strtolower('Morecontent');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContent.xml');

	$pluginName = strtolower('Morecontent2');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContent.xml');

	$pluginName = strtolower('MorecontentPublication');
	$pluginSignature = $extensionName . '_' . $pluginName;
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/MoreContent.xml');
}

$pluginName = strtolower('Similarcontent');
$pluginSignature = $extensionName . '_' . $pluginName;
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/SimilarContent.xml');
