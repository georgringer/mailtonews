<?php

namespace GeorgRinger\Mailtonews\Command;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Georg Ringer <typo3@ringerge.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use GeorgRinger\Mailtonews\Service\SmtpService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command controller which is called in CLI mode.
 *
 * @author Georg Ringer <typo3@ringerge.org>
 */
class ImportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var \Tx_Extbase_Configuration_ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * TS settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * This runs something
	 *
	 * @param string $mode Define one of the modes
	 */
	public function runCommand($mode) {
		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
			'Mailtonews',
			'');

		$config = $this->getConfiguration($mode);

		$this->outputLine('Import is starting for mode "%s"', array($mode));
		$this->outputLine('==================================');

		/** @var SmtpService $smtpService */
		$smtpService = GeneralUtility::makeInstance('GeorgRinger\\Mailtonews\\Service\\SmtpService');
		$smtpService->setConfiguration($config['configuration']);
		$smtpService->setUsername($config['username']);
		$smtpService->setPassword($config['password']);
		$smtpService->setHost($config['host']);
		$smtpService->setObjectManager($this->objectManager);

		$smtpService->import();

		try {
		} catch (\Exception $exception) {
			$this->outputLine('An error occurred' . PHP_EOL . '=> ' . $exception->getMessage());
		}

	}


	/**
	 * @param string $mode
	 * @return array configuration
	 * @throws \Exception
	 */
	protected function getConfiguration($mode) {
		if (!function_exists('imap_search')) {
			throw new \RuntimeException('The function "imap_search" does not exist. Please check if IMAP support is also available in CLI context!');
		}

		if (!is_array($this->settings)) {
			throw new \RuntimeException('Configuration ERROR: No TypoScript defined');
		}
		if (!is_array($this->settings['mode'])) {
			throw new \RuntimeException('Configuration ERROR:  No TypoScript node \'mode.\' defined!');
		}
		if (!is_array($this->settings['mode'][$mode])) {
			throw new \RuntimeException(sprintf('Configuration ERROR:  No TypoScript node for mode "%s" defined!', $mode));
		}

		$configuration = $this->settings['mode'][$mode];
		if (empty($configuration['username'])) {
			throw new \RuntimeException(sprintf('Configuration ERROR:  No username defined for mode "%s"!', $mode));
		}
		if (empty($configuration['password'])) {
			throw new \RuntimeException(sprintf('Configuration ERROR:  No password defined for mode "%s"!', $mode));
		}
		if (empty($configuration['host'])) {
			throw new \RuntimeException(sprintf('Configuration ERROR:  No host defined for mode "%s"!', $mode));
		}

		return $configuration;
	}
}