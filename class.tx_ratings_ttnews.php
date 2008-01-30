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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hooks to tt_news.
 *
 * $Id: class.tx_ratings_ttnews.php 8066 2008-01-28 12:38:14Z liels_bugs $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class tx_ratings_ttnews
 *   60:     public function __construct()
 *   73:     function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('ratings', 'class.tx_ratings_api.php'));

/**
 * This clas provides hook to tt_news to add extra markers.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package TYPO3
 * @subpackage comments
 */
class tx_ratings_ttnews {
	/**
	 * API object
	 *
	 * @var	tx_ratings_api
	 */
	var $apiObj;

	/**
	 * Creates an instance of this class
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->apiObj = t3lib_div::makeInstance('tx_ratings_api');
	}

	/**
	 * Processes comments-specific markers for tt_news
	 *
	 * @param	array		$markerArray	Array with merkers
	 * @param	array		$row	tt_news record
	 * @param	array		$lConf	Configuration array for current tt_news view
	 * @param	tx_ttnews		$pObj	Reference to parent object
	 * @return	array		Modified marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		/* @var $pObj tx_ttnews */
		$markerArray['###TX_RATINGS###'] = $this->apiObj->getRatingDisplay('tt_news_' . $row['uid']);
		return $markerArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ttnews.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ratings/class.tx_ratings_ttnews.php']);
}

?>