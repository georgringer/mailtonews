<?php

namespace GeorgRinger\Mailtonews\Service\Import;

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

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Basic implementation of a mail import to news records
 *
 * @author Georg Ringer <typo3@ringerge.org>
 */
class BasicImport implements ImportInterface {

	/** @var  \GeorgRinger\Mailtonews\Service\SmtpService */
	protected $smtpService;

	public function save(\IncomingMail $mail, \GeorgRinger\Mailtonews\Service\SmtpService $smtpService) {
		$this->smtpService = $smtpService;
		$data = array($this->extractDataFromMail($mail));

		/** @var \Tx_News_Domain_Service_NewsImportService $newsImportService */
		$newsImportService = $smtpService->getObjectManager()->get('Tx_News_Domain_Service_NewsImportService');
		$newsImportService->import($data);

//		$smtpService->getMailbox()->markMailAsUnread($mail->id);
	}


	/**
	 * Get all needed data out of the mail
	 * @param \IncomingMail $mail
	 * @return array
	 */
	protected function extractDataFromMail(\IncomingMail $mail) {
		$data = array();

		$configuration = $this->smtpService->getConfiguration();
		if (isset($configuration['defaultValues']) && is_array($configuration['defaultValues'])) {
			foreach ($configuration['defaultValues'] as $fieldName => $value) {
				$data[$fieldName] = $value;
			}
		}

		$data['import_id'] = $mail->id;
		$data['import_source'] = $this->smtpService->getHost();
		$data['title'] = $mail->subject;
		$data['datetime'] = strtotime($mail->date);
		$data['author'] = $mail->fromName;
		$data['author_email'] = $mail->fromAddress;

		if (!empty($mail->textHtml)) {
			$data['bodytext'] = $mail->textHtml;
		} else {
			$data['bodytext'] = $mail->textPlain;
		}

		$relatedFiles = $images = array();
		$attachments = $mail->getAttachments();
		foreach ($attachments as $attachment) {
			/** @var \IncomingMailAttachment $attachment */
			$fileInformation = pathinfo($attachment->name);
			if (GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], strtolower($fileInformation['extension']))) {
				$images[] = $attachment;
			} else {
				$relatedFiles[] = $attachment;
			}
		}

		$this->handleRelatedFiles($relatedFiles, $data);

		if (is_array($configuration['imagesAsContentElement']) && $configuration['imagesAsContentElement']['_typoScriptNodeValue'] == 1) {
			$this->handleImagesAsContentElement($images, $data);
		} else {
			$this->handleImagesAsMedia($images, $data);
		}
		return $data;
	}

	protected function handleImagesAsContentElement(array $attachments, array &$data) {
		if (count($attachments) == 0) {
			return;
		}
		$newElement = $imageElements = array();

		/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
		$dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');

		// create new content element "image"
		$newElement['tt_content']['NEW'] = array(
			'pid' => $data['pid'],
			'CType' => 'image',
		);

		$dataHandler->start($newElement, array());
		$dataHandler->admin = 1;
		$dataHandler->process_datamap();
		$data['content_elements'] = $dataHandler->substNEWwithIDs['NEW'];


		/** @var ResourceFactory $resourceFactory */
		$resourceFactory = $this->smtpService->getObjectManager()->get('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');

		foreach ($attachments as $key => $mediaElement) {
			/** @var \IncomingMailAttachment $mediaElement */

			// move file to local storage
			$finalPath = PATH_site . 'fileadmin/import/' . $data['import_id'] . '_' . $mediaElement->name;
			GeneralUtility::upload_copy_move($mediaElement->filePath, $finalPath);


			$sysFile = $resourceFactory->retrieveFileOrFolderObject($finalPath);


			$imageElements['sys_file_reference']['NEW8181' . $key] = array(
				'uid_local' => $sysFile->getUid(),
				'uid_foreign' => $data['content_elements'],
				'tablenames' => 'tt_content',
				'fieldname' => 'image',
				'pid' => $newElement['tt_content']['NEW']['pid'], // parent id of the parent page
				'table_local' => 'sys_file',
			);
		}

		// Save sys_file_references and update content element accordingly
		$sysFileKeys = implode(',', array_keys($imageElements['sys_file_reference']));

		$imageElements['tt_content'][$data['content_elements']] = array(
			'image' => $sysFileKeys,
			'imagecols' => $this->getImageColByCount(count(array_keys($imageElements['sys_file_reference'])))
		);
		$dataHandler->start($imageElements, array());
		$dataHandler->admin = 1;
		$dataHandler->process_datamap();

//			@todo how to return errors in errorlog
		$error = ($dataHandler->errorLog);
		if (!empty($error)) {
			throw new \Exception('ERROR happend while inserting content element:' . $error);
		}
	}

	/**
	 * Add images as normal media elements
	 *
	 * @param array $attachments
	 * @param array $data
	 * @return void
	 */
	protected function handleImagesAsMedia(array $attachments, array &$data) {
		$toBeImported = array();
		foreach ($attachments as $mediaElement) {
			/** @var \IncomingMailAttachment $mediaElement */
			$toBeImported[] = array(
				'type' => 0,
				'image' => str_replace(PATH_site, '', $mediaElement->filePath)
			);
		}
		$data['media'] = $toBeImported;
	}

	/**
	 * Import related files
	 *
	 * @param array $relatedFiles
	 * @param array $data
	 */
	protected function handleRelatedFiles(array $relatedFiles, array &$data) {
		$toBeImported = array();
		foreach ($relatedFiles as $relatedFile) {
			/** @var \IncomingMailAttachment $relatedFile */
			$toBeImported[] = array(
				'file' => str_replace(PATH_site, '', $relatedFile->filePath)
			);
		}
		$data['related_files'] = $toBeImported;
	}

	/**
	 * Get correct col count
	 *
	 * @param integer $count
	 * @return int
	 */
	protected function getImageColByCount($count) {
		$cols = $count;
		if ($count == 5) {
			$cols = 6;
		} elseif($count > 6) {
			$cols = 6;
		}

		return $cols;
	}

}