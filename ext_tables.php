<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}


/***************
 * Default TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Mailtonews');

