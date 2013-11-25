<?php

namespace GeorgRinger\Mailtonews\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Georg Ringer <typo3@ringerge.org>
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

use GeorgRinger\Mailtonews\Service\Import\ImportInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SMTP Service to get the correct mails out of the mailbox.
 *
 * @author Georg Ringer <typo3@ringerge.org>
 */
class SmtpService {

	protected $username = '';
	protected $password = '';
	protected $host = '';
	protected $configuration = array();

	/** @var  \ImapMailbox */
	protected $mailbox;
	protected $tempDirectory;

	protected $out = array();
	/** @var  \TYPO3\CMS\Extbase\Object\ObjectManagerInterface */
	protected $objectManager;

	public function import() {
		$this->init();

		$mailsIds = $this->mailbox->searchMailBox($this->configuration['searchCriteria']);
		if (!$mailsIds) {
			return 'Mailbox is empty';
		}
		$importerClass = 'GeorgRinger\\Mailtonews\\Service\\Import\\BasicImport';
		if (isset($this->configuration['importerClass'])) {
			$importerClass = $this->configuration['importerClass'];
		}

		/** @var ImportInterface $importer */
		$importer = GeneralUtility::makeInstance($importerClass);
		if (!$importer instanceof ImportInterface) {
			throw new \Exception(sprintf('The class "%s" must implement ImportInterface', $importerClass));
		}

		foreach ($mailsIds as $mailId) {
			$mail = $this->mailbox->getMail($mailId, $this);
			$importer->save($mail, $this);
		}
	}

	public function getOut() {
		return $this->out;
	}

	protected function init() {
		// Create temp directory if not existing
		$tempDir = PATH_site . 'typo3temp/mailtonews/';
		if (!is_dir($tempDir)) {
			$result = GeneralUtility::mkdir($tempDir);
			if (!$result) {
				throw new \Exception(sprintf('Directory "%s" could not be created', $tempDir));
			}
		}
		$this->tempDirectory = $tempDir;

		GeneralUtility::requireOnce(ExtensionManagementUtility::extPath('mailtonews') . 'Resources/Private/Php/php-imap/src/ImapMailbox.php');
		$this->mailbox = new \ImapMailbox($this->host, $this->username, $this->password, $this->tempDirectory);

	}

	/**
	 * @param array $configuration
	 */
	public function setConfiguration(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @return \ImapMailbox
	 */
	public function getMailbox() {
		return $this->mailbox;
	}

	/**
	 * @return mixed
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @return mixed
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 */
	public function setObjectManager($objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	public function getObjectManager() {
		return $this->objectManager;
	}


}