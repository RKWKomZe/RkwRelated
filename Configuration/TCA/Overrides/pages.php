<?php
if (
	! \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_projects')
	&& ! \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_pdf2content')
) {

	$GLOBALS['TCA']['pages']['columns']['categories']['config']['eval'] = 'required';
}