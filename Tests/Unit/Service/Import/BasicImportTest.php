<?php

namespace GeorgRinger\Mailtonews\Service\Import;

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

class BasicImportTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @test
	 * @dataProvider correctImageColsAreCalculatedDataProvider
	 */
	public function correctImageColsAreCalculated($given, $expected) {
		$basicImport = $this->getAccessibleMock('GeorgRinger\\Mailtonews\\Service\\Import\\BasicImport', array('dummy'));

		$result = $basicImport->_call('getImageColByCount', $given);

		$this->assertEquals($result, $expected);
	}

	public function correctImageColsAreCalculatedDataProvider() {
		return array(
			'1col' => array(1, 1),
			'2col' => array(2, 2),
			'3col' => array(3, 3),
			'4col' => array(4, 4),
			'5col' => array(5, 6),
			'6col' => array(6, 6),
			'7col' => array(7, 6),
			'10col' => array(10, 6),
		);
	}
}

?>