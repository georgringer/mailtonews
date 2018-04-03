<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mailtonews".
 *
 * Auto generated 23-09-2013 06:26
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Import mails to EXT:news',
	'description' => 'Most simple way to write news entries, by just sending a mail to a specific email address',
	'category' => 'be',
	'author' => 'Georg Ringer',
	'author_email' => 'typo3@ringerge.org',
	'shy' => '',
	'dependencies' => 'extbase,news',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 1,
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '2.2.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.0.0-8.9.99',
			'php' => '5.3.0-0.0.0',
			'extbase' => '',
			'news' => '',
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(),
);
