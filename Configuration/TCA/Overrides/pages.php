<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}


if (
	! \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')
	&& ! \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bm_pdf2content')
) {

	$GLOBALS['TCA']['pages']['columns']['categories']['config']['eval'] = 'required';
}