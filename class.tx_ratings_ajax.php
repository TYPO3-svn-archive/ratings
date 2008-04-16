<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dmitry Dulepov (dmitry@typo3.org)
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
/**
* class.tx_ratings_ajax.php
*
* Comment management script.
*
* $Id: class.tx_ratings_ajax.php 7093 2007-10-24 12:39:55Z liels_bugs $
*
* @author Dmitry Dulepov <dmitry@typo3.org>
*/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   64: class tx_ratings_ajax
 *   75:     public function __construct()
 *  118:     public function main()
 *  130:     protected function updateRating()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
require_once(t3lib_extMgm::extPath('ratings', 'class.tx_ratings_api.php'));
//require_once(PATH_site . 't3lib/class.t3lib_tcemain.php');
require_once(t3lib_extMgm::extPath('ratings', 'ext_tables.php'));
require_once(t3lib_extMgm::extPath('ratings', 'tca.php'));

/**
 * Comment management script.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage tx_comments
 */
class tx_ratings_ajax {
	protected $ref;
	protected $pid;
	protected $rating;
	protected $conf;

	/**
	 * Initializes the class
	 *
	 * @return	void
	 */
	public function __construct() {
		$data_str = t3lib_div::_GP('data');
		$data = unserialize(base64_decode($data_str));

		$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
		$GLOBALS['LANG']->init($data['lang'] ? $data['lang'] : 'default');
		$GLOBALS['LANG']->includeLLFile('EXT:ratings/locallang_ajax.xml');

		tslib_eidtools::connectDB();

		// Sanity check
		$this->rating = t3lib_div::_GP('rating');
		if (!t3lib_div::testInt($this->rating)) {
			echo $GLOBALS['LANG']->getLL('bad_rating_value');
			exit;
		}
		$this->ref = t3lib_div::_GP('ref');
		if (trim($this->ref) == '') {
			echo $GLOBALS['LANG']->getLL('bad_ref_value');
			exit;
		}
		$check = t3lib_div::_GP('check');
		if (md5($this->ref . $this->rating . $data_str . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) != $check) {
			echo $GLOBALS['LANG']->getLL('wrong_check_value');
			exit;
		}
		$this->conf = $data['conf'];
		if (!is_array($this->conf)) {
			echo $GLOBALS['LANG']->getLL('bad_conf_value');
			exit;
		}
		$this->pid = $data['pid'];
		if (!t3lib_div::testInt($this->pid)) {
			echo $GLOBALS['LANG']->getLL('bad_pid_value');
			exit;
		}
	}

	/**
	 * Main processing function of eID script
	 *
	 * @return	void
	 */
	public function main() {
		$this->updateRating();
		// Clear cache. TCEmain requires $TCA for this, so we just do it ourselves.
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'page_id=' . $this->pid);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pagesection', 'page_id=' . $this->pid);
	}

	/**
	 * Updates rating data and outputs new result
	 *
	 * @return	void
	 */
	protected function updateRating() {
		$apiObj = t3lib_div::makeInstance('tx_ratings_api');
		/* @var $apiObj tx_ratings_api */

		if ($this->conf['disableIpCheck'] || !$apiObj->isVoted($this->ref, $this->conf)) {

			// Do everything inside transaction
			$GLOBALS['TYPO3_DB']->sql_query('START TRANSACTION');
			$dataWhere = 'pid=' . intval($this->conf['storagePid']) .
						' AND reference=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->ref, 'tx_ratings_data') .
						$apiObj->enableFields('tx_ratings_data');
			list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t',
					'tx_ratings_data', $dataWhere);
			if ($row['t'] > 0) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_ratings_data', $dataWhere,
					array(
						'vote_count' => 'vote_count+1',
						'rating' => 'rating+' . intval($this->rating),
						'tstamp' => time(),
					), 'vote_count,rating');
			}
			else {
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_ratings_data',
					array(
						'pid' => $this->conf['storagePid'],
						'crdate' => time(),
						'tstamp' => time(),
						'reference' => $this->ref,
						'vote_count' => 1,
						'rating' => $this->rating,
					));
			}
			// Call hook if ratings is updated
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ratings']['updateRatings'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ratings']['updateRatings'] as $userFunc) {
					$params = array(
						'pObj' => &$this,
						'pid' => $this->pid,
						'ref' => $this->ref,
					);
					t3lib_div::callUserFunction($userFunc, $params, $this);
				}
			}
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_ratings_iplog',
				array(
					'pid' => $this->conf['storagePid'],
					'crdate' => time(),
					'tstamp' => time(),
					'reference' => $this->ref,
					'ip' => $apiObj->getCurrentIp(),
				));
			$GLOBALS['TYPO3_DB']->sql_query('COMMIT');
		}

		// Get rating display
		$this->conf['mode'] = 'static';
		echo $apiObj->getRatingDisplay($this->ref, $this->conf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ajax.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ajax.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_ratings_ajax');
$SOBE->main();

?>